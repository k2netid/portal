<?php

declare(strict_types=1);

namespace Modules\Core\System\Http\Controllers\Api;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Core\System\Contracts\OutboundWebhookPortInterface;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\ActivityLog;
use Modules\Core\System\Models\ContentType;
use Modules\Core\System\Models\DynamicRecord;
use Modules\Core\System\Support\DataModelFieldRulesBuilder;
use Modules\Core\System\Support\DynamicOpenApiBuilder;
use Modules\Core\System\Support\SqlLikeEscape;

class DataModelApiController extends BaseApiController
{
    /**
     * Get the dynamic content type / data model by slug.
     */
    protected function getContentType(string $slug): ContentType
    {
        /** @var ContentType|null $contentType */
        $contentType = ContentType::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (! $contentType) {
            abort(404, "Data model '{$slug}' not found or inactive.");
        }

        return $contentType;
    }

    /**
     * Resolve validation rules dynamically based on content type fields.
     *
     * @return array<string, string>
     */
    protected function resolveValidationRules(ContentType $contentType): array
    {
        return DataModelFieldRulesBuilder::rulesFor($contentType);
    }

    /**
     * Hydrate relational fields for a list of records or single record.
     *
     * @param  array<int, mixed>|DynamicRecord|LengthAwarePaginator  $records
     */
    protected function hydrateRelations(ContentType $contentType, mixed $records): mixed
    {
        $fields = $contentType->fields;
        if (! is_array($fields)) {
            return $records;
        }

        $relationFields = [];
        foreach ($fields as $field) {
            if (is_array($field) && ($field['type'] ?? '') === 'relation') {
                $slug = (string) ($field['slug'] ?? '');
                $targetType = (string) ($field['target_type'] ?? '');
                $relationMode = (string) ($field['relation_mode'] ?? 'single');
                if ($slug !== '' && $targetType !== '') {
                    $relationFields[$slug] = [
                        'target_type' => $targetType,
                        'relation_mode' => $relationMode,
                    ];
                }
            }
        }

        if (empty($relationFields)) {
            return $records;
        }

        $isSingle = $records instanceof DynamicRecord;
        $items = $isSingle ? [$records] : ($records instanceof LengthAwarePaginator ? $records->items() : $records);

        // Collect target IDs grouped by target_type
        $targetIdsByType = [];
        foreach ($items as $item) {
            if (! ($item instanceof DynamicRecord)) {
                continue;
            }
            $data = is_array($item->data) ? $item->data : [];
            foreach ($relationFields as $fieldSlug => $config) {
                $val = $data[$fieldSlug] ?? null;
                if (! empty($val)) {
                    $targetType = $config['target_type'];
                    if (! isset($targetIdsByType[$targetType])) {
                        $targetIdsByType[$targetType] = [];
                    }
                    if (is_array($val)) {
                        foreach ($val as $subVal) {
                            if (is_string($subVal) && $subVal !== '') {
                                $targetIdsByType[$targetType][] = $subVal;
                            }
                        }
                    } elseif (is_string($val) && $val !== '') {
                        $targetIdsByType[$targetType][] = $val;
                    }
                }
            }
        }

        // Fetch related entities in batch
        $resolvedEntitiesByType = [];
        foreach ($targetIdsByType as $targetType => $ids) {
            $uniqueIds = array_unique($ids);
            if (empty($uniqueIds)) {
                continue;
            }

            // Check if target is another ContentType slug
            $targetContentType = ContentType::where('slug', $targetType)->first();
            if ($targetContentType) {
                $relatedRecords = DynamicRecord::where('content_type_id', $targetContentType->id)
                    ->whereIn('id', $uniqueIds)
                    ->get();
                $resolvedEntitiesByType[$targetType] = $relatedRecords->keyBy('id')->all();
            }
        }

        // Attach resolved relations to records as custom attribute
        foreach ($items as $item) {
            if (! ($item instanceof DynamicRecord)) {
                continue;
            }
            $data = is_array($item->data) ? $item->data : [];
            $relationsData = [];
            foreach ($relationFields as $fieldSlug => $config) {
                $val = $data[$fieldSlug] ?? null;
                $targetType = $config['target_type'];
                if (! empty($val) && isset($resolvedEntitiesByType[$targetType])) {
                    if (is_array($val)) {
                        $relationsData[$fieldSlug] = [];
                        foreach ($val as $id) {
                            if (isset($resolvedEntitiesByType[$targetType][$id])) {
                                $relationsData[$fieldSlug][] = $resolvedEntitiesByType[$targetType][$id];
                            }
                        }
                    } elseif (isset($resolvedEntitiesByType[$targetType][$val])) {
                        $relationsData[$fieldSlug] = $resolvedEntitiesByType[$targetType][$val];
                    }
                }
            }
            $item->setAttribute('_relations', (object) $relationsData);
        }

        return $isSingle ? $items[0] : $records;
    }

    /**
     * GET /api/v1/dynamic/{slug}
     * List all dynamic records for a data model.
     */
    public function index(Request $request, string $slug): JsonResponse
    {
        $contentType = $this->getContentType($slug);

        $query = DynamicRecord::where('content_type_id', $contentType->id);

        // 1. Dynamic Searching (SQLite & MySQL compatible JSON search)
        $search = $request->query('search');
        if (is_string($search) && $search !== '') {
            $fields = $contentType->fields;
            $searchableFields = [];
            if (is_array($fields)) {
                foreach ($fields as $field) {
                    if (is_array($field)) {
                        $slugVal = $field['slug'] ?? '';
                        $searchableFields[] = is_scalar($slugVal) ? (string) $slugVal : '';
                    }
                }
            }

            if (! empty($searchableFields)) {
                $searchLower = mb_strtolower($search, 'UTF-8');
                $likePattern = SqlLikeEscape::contains($searchLower);
                $driver = DB::connection()->getDriverName();

                if ($driver === 'pgsql') {
                    $query->whereRaw(
                        "LOWER(data::text) LIKE ? ESCAPE '\\'",
                        [$likePattern]
                    );
                } else {
                    $query->where(function ($q) use ($searchableFields, $likePattern, $driver): void {
                        foreach ($searchableFields as $field) {
                            if ($field === '' || ! preg_match('/^[a-zA-Z0-9_]+$/', $field)) {
                                continue;
                            }
                            $jsonPath = '$.'.$field;
                            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                                $q->orWhereRaw(
                                    "LOWER(JSON_UNQUOTE(JSON_EXTRACT(`data`, ?))) LIKE ? ESCAPE '\\'",
                                    [$jsonPath, $likePattern]
                                );
                            } else {
                                $q->orWhereRaw(
                                    "LOWER(json_extract(data, ?)) LIKE ? ESCAPE '\\'",
                                    [$jsonPath, $likePattern]
                                );
                            }
                        }
                    });
                }
            }
        }

        // 2. Field-specific Filtering (?filter[field]=val)
        $filter = $request->query('filter');
        if (is_array($filter)) {
            foreach ($filter as $fieldKey => $fieldVal) {
                if (is_string($fieldKey) && preg_match('/^[a-zA-Z0-9_]+$/', $fieldKey) && is_scalar($fieldVal)) {
                    $query->where("data->{$fieldKey}", (string) $fieldVal);
                }
            }
        }

        // 3. Dynamic Sorting
        $sortBy = $request->query('sort_by');
        $sortOrder = $request->query('sort_order', 'desc');
        if (is_string($sortBy) && $sortBy !== '' && preg_match('/^[a-zA-Z0-9_]+$/', $sortBy)) {
            $sortDirection = is_string($sortOrder) && strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';
            $query->orderBy("data->{$sortBy}", $sortDirection);
        } else {
            $query->latest();
        }

        // 4. Paginate output
        $perPage = (int) $request->query('per_page', 15);
        $records = $query->paginate($perPage > 0 ? $perPage : 15);

        // 5. Hydrate Relations
        $this->hydrateRelations($contentType, $records);

        return $this->success($records, 'Dynamic records retrieved successfully');
    }

    /**
     * POST /api/v1/dynamic/{slug}
     * Create a new dynamic record.
     */
    public function store(Request $request, string $slug): JsonResponse
    {
        $contentType = $this->getContentType($slug);
        $rules = $this->resolveValidationRules($contentType);

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->error('Validasi Gagal', 422, $validator->errors()->toArray());
        }

        $payload = $validator->validated();

        $record = DynamicRecord::create([
            'content_type_id' => $contentType->id,
            'data' => $payload,
        ]);

        $this->hydrateRelations($contentType, $record);
        $this->dispatchLifecycle('created', $contentType, $record, $payload);

        return $this->success($record, 'Dynamic record created successfully', 201);
    }

    /**
     * GET /api/v1/dynamic/{slug}/{id}
     * Get a single dynamic record by ID.
     */
    public function show(string $slug, string $id): JsonResponse
    {
        $contentType = $this->getContentType($slug);

        /** @var DynamicRecord|null $record */
        $record = DynamicRecord::where('content_type_id', $contentType->id)
            ->where('id', $id)
            ->first();

        if (! $record) {
            return $this->error('Record not found', 404);
        }

        $this->hydrateRelations($contentType, $record);

        return $this->success($record, 'Dynamic record retrieved successfully');
    }

    /**
     * PUT /api/v1/dynamic/{slug}/{id}
     * Update a dynamic record.
     */
    public function update(Request $request, string $slug, string $id): JsonResponse
    {
        $contentType = $this->getContentType($slug);

        /** @var DynamicRecord|null $record */
        $record = DynamicRecord::where('content_type_id', $contentType->id)
            ->where('id', $id)
            ->first();

        if (! $record) {
            return $this->error('Record not found', 404);
        }

        $existingData = is_array($record->data) ? $record->data : [];
        $mergedData = array_merge($existingData, $request->all());

        $rules = $this->resolveValidationRules($contentType);
        $validator = Validator::make($mergedData, $rules);
        if ($validator->fails()) {
            return $this->error('Validasi Gagal', 422, $validator->errors()->toArray());
        }

        $payload = $validator->validated();

        $record->update([
            'data' => $payload,
        ]);

        $this->hydrateRelations($contentType, $record);
        $this->dispatchLifecycle('updated', $contentType, $record, $payload);

        return $this->success($record, 'Dynamic record updated successfully');
    }

    /**
     * DELETE /api/v1/dynamic/{slug}/{id}
     * Delete a dynamic record.
     */
    public function destroy(string $slug, string $id): JsonResponse
    {
        $contentType = $this->getContentType($slug);

        /** @var DynamicRecord|null $record */
        $record = DynamicRecord::where('content_type_id', $contentType->id)
            ->where('id', $id)
            ->first();

        if (! $record) {
            return $this->error('Record not found', 404);
        }

        $record->delete();
        $this->dispatchLifecycle('deleted', $contentType, $id);

        return $this->success(null, 'Dynamic record deleted successfully');
    }

    /**
     * Dispatch webhook and activity log for dynamic record operations.
     *
     * @param  array<string, mixed>|null  $payload
     */
    protected function dispatchLifecycle(string $action, ContentType $contentType, DynamicRecord|string $recordOrId, ?array $payload = null): void
    {
        $event = "dynamic.{$contentType->slug}.{$action}";
        $recordData = $recordOrId instanceof DynamicRecord ? $recordOrId->toArray() : ['id' => $recordOrId];

        try {
            if (interface_exists(OutboundWebhookPortInterface::class)) {
                /** @var OutboundWebhookPortInterface $dispatcher */
                $dispatcher = app(OutboundWebhookPortInterface::class);
                $dispatcher->dispatch($event, [
                    'model' => [
                        'id' => $contentType->id,
                        'slug' => $contentType->slug,
                        'name' => $contentType->name,
                    ],
                    'record' => $recordData,
                ]);
            }
        } catch (\Throwable $e) {
            // Non-blocking webhook dispatch failure
        }

        try {
            if (class_exists(ActivityLog::class)) {
                $model = $recordOrId instanceof DynamicRecord ? $recordOrId : null;
                ActivityLog::log(
                    $action,
                    $model,
                    [
                        'slug' => $contentType->slug,
                        'payload' => $payload,
                    ],
                    null,
                    ucfirst($action)." dynamic record in '{$contentType->name}'"
                );
            }
        } catch (\Throwable $e) {
            // Non-blocking activity log failure
        }
    }

    /**
     * GET /api/v1/manage/infra/models/types
     * List all dynamic data model schemas.
     */
    public function listTypes(): JsonResponse
    {
        $types = ContentType::latest()->get();

        return $this->success($types, 'Data models retrieved successfully');
    }

    /**
     * POST /api/v1/manage/infra/models/types
     * Create a new dynamic data model schema.
     */
    public function storeType(Request $request): JsonResponse
    {
        $rules = [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:sys_content_types,slug',
            'description' => 'nullable|string',
            'fields' => 'required|array',
            'fields.*.name' => 'required|string',
            'fields.*.slug' => 'required|string',
            'fields.*.type' => 'required|string|in:'.implode(',', DataModelFieldRulesBuilder::allowedFieldTypes()),
            'fields.*.options' => 'nullable|array',
            'fields.*.options.*' => 'string',
            'fields.*.is_required' => 'nullable|boolean',
            'fields.*.target_type' => 'nullable|string',
            'fields.*.relation_mode' => 'nullable|string|in:single,multiple',
            'fields.*.placeholder' => 'nullable|string',
            'fields.*.default_value' => 'nullable',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->error('Validasi Gagal', 422, $validator->errors()->toArray());
        }

        $payload = $validator->validated();

        $reserved = $this->reservedSlugResponse((string) $payload['slug']);
        if ($reserved instanceof JsonResponse) {
            return $reserved;
        }

        $type = ContentType::create([
            'name' => $payload['name'],
            'slug' => $payload['slug'],
            'description' => $payload['description'] ?? null,
            'fields' => $payload['fields'],
            'is_active' => true,
        ]);

        return $this->success($type, 'Data model created successfully', 201);
    }

    /**
     * GET /api/v1/manage/infra/models/types/{id}
     * Get a single data model schema by ID.
     */
    public function showType(string $id): JsonResponse
    {
        $type = ContentType::find($id);

        if (! $type) {
            return $this->error('Data model not found', 404);
        }

        return $this->success($type, 'Data model retrieved successfully');
    }

    /**
     * GET /api/v1/manage/infra/models/types/by-slug/{slug}
     */
    public function showTypeBySlug(string $slug): JsonResponse
    {
        $type = ContentType::query()->where('slug', $slug)->first();

        if (! $type) {
            return $this->error('Data model not found', 404);
        }

        return $this->success($type, 'Data model retrieved successfully');
    }

    /**
     * GET /api/v1/manage/infra/models/types/by-slug/{slug}/openapi
     * OpenAPI 3 document for /api/v1/dynamic/{slug} (field-driven).
     */
    public function openApiBySlug(string $slug, DynamicOpenApiBuilder $builder): JsonResponse
    {
        $type = ContentType::query()->where('slug', $slug)->where('is_active', true)->first();

        if (! $type) {
            return $this->error('Data model not found or inactive', 404);
        }

        return $this->success($builder->buildFor($type), 'OpenAPI document generated');
    }

    /**
     * GET /api/v1/manage/infra/models/types/openapi-index
     */
    public function openApiIndex(): JsonResponse
    {
        $types = ContentType::query()
            ->where('is_active', true)
            ->orderBy('slug')
            ->get(['id', 'slug', 'name']);

        $index = $types->map(fn (ContentType $type): array => [
            'slug' => $type->slug,
            'name' => $type->name,
            'openapi_path' => '/api/v1/manage/infra/models/types/by-slug/'.$type->slug.'/openapi',
            'dynamic_api_base' => '/api/v1/dynamic/'.$type->slug,
            'export_filename' => 'dynamic-'.$type->slug.'.openapi.json',
        ])->values()->all();

        return $this->success($index, 'Dynamic OpenAPI index');
    }

    /**
     * GET /api/v1/manage/infra/models/types/{id}/validation-rules
     */
    public function typeValidationRules(string $id): JsonResponse
    {
        $type = ContentType::find($id);

        if (! $type) {
            return $this->error('Data model not found', 404);
        }

        return $this->success([
            'content_type_id' => $type->id,
            'slug' => $type->slug,
            'validation_rules' => DataModelFieldRulesBuilder::rulesFor($type),
            'field_types' => DataModelFieldRulesBuilder::allowedFieldTypes(),
        ], 'Validation rules resolved');
    }

    /**
     * PUT /api/v1/manage/infra/models/types/{id}
     * Update an existing data model schema.
     */
    public function updateType(Request $request, string $id): JsonResponse
    {
        $type = ContentType::find($id);

        if (! $type) {
            return $this->error('Data model not found', 404);
        }

        $rules = [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:sys_content_types,slug,'.$id,
            'description' => 'nullable|string',
            'fields' => 'required|array',
            'fields.*.name' => 'required|string',
            'fields.*.slug' => 'required|string',
            'fields.*.type' => 'required|string|in:'.implode(',', DataModelFieldRulesBuilder::allowedFieldTypes()),
            'fields.*.options' => 'nullable|array',
            'fields.*.options.*' => 'string',
            'fields.*.is_required' => 'nullable|boolean',
            'fields.*.target_type' => 'nullable|string',
            'fields.*.relation_mode' => 'nullable|string|in:single,multiple',
            'fields.*.placeholder' => 'nullable|string',
            'fields.*.default_value' => 'nullable',
            'is_active' => 'nullable|boolean',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->error('Validasi Gagal', 422, $validator->errors()->toArray());
        }

        $payload = $validator->validated();

        $reserved = $this->reservedSlugResponse((string) $payload['slug']);
        if ($reserved instanceof JsonResponse) {
            return $reserved;
        }

        $type->update([
            'name' => $payload['name'],
            'slug' => $payload['slug'],
            'description' => $payload['description'] ?? null,
            'fields' => $payload['fields'],
            'is_active' => isset($payload['is_active']) ? (bool) $payload['is_active'] : $type->is_active,
        ]);

        return $this->success($type, 'Data model updated successfully');
    }

    /**
     * DELETE /api/v1/manage/infra/models/types/{id}
     * Delete an existing data model schema.
     */
    public function destroyType(string $id): JsonResponse
    {
        $type = ContentType::find($id);

        if (! $type) {
            return $this->error('Data model not found', 404);
        }

        $type->delete();

        return $this->success(null, 'Data model deleted successfully');
    }

    private function reservedSlugResponse(string $slug): ?JsonResponse
    {
        if (! ContentType::isReservedSlug($slug)) {
            return null;
        }

        return $this->error(
            'This slug is reserved for CMS packs or kernel entities. Data Studio is for operational records, not editorial content types.',
            422,
            ['slug' => ['This slug is reserved.']],
            'DATA_MODEL_SLUG_RESERVED',
        );
    }
}
