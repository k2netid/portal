<?php

declare(strict_types=1);

namespace Modules\Core\System\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\ContentType;
use Modules\Core\System\Models\DynamicRecord;
use Modules\Core\System\Support\ContentTypeFieldRulesBuilder;
use Modules\Core\System\Support\DynamicOpenApiBuilder;
use Modules\Core\System\Support\SqlLikeEscape;

class DynamicApiController extends BaseApiController
{
    /**
     * Get the dynamic content type by slug.
     */
    protected function getContentType(string $slug): ContentType
    {
        /** @var ContentType|null $contentType */
        $contentType = ContentType::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (! $contentType) {
            abort(404, "Dynamic content type '{$slug}' not found or inactive.");
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
        return ContentTypeFieldRulesBuilder::rulesFor($contentType);
    }

    /**
     * GET /api/v1/dynamic/{slug}
     * List all dynamic records for a content type.
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

        // 2. Dynamic Sorting
        $sortBy = $request->query('sort_by');
        $sortOrder = $request->query('sort_order', 'desc');
        if (is_string($sortBy) && $sortBy !== '' && preg_match('/^[a-zA-Z0-9_]+$/', $sortBy)) {
            $sortDirection = is_string($sortOrder) && strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';
            $query->orderBy("data->{$sortBy}", $sortDirection);
        } else {
            $query->latest();
        }

        // 3. Paginate output
        $perPage = (int) $request->query('per_page', 15);
        $records = $query->paginate($perPage > 0 ? $perPage : 15);

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

        $rules = $this->resolveValidationRules($contentType);
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->error('Validasi Gagal', 422, $validator->errors()->toArray());
        }

        $payload = $validator->validated();

        // Merge updated fields with existing JSON data to support partial updates
        $existingData = $record->data;
        $updatedData = is_array($existingData) ? array_merge($existingData, $payload) : $payload;

        $record->update([
            'data' => $updatedData,
        ]);

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

        return $this->success(null, 'Dynamic record deleted successfully');
    }

    /**
     * GET /api/v1/manage/infra/cck/types
     * List all dynamic content type schemas.
     */
    public function listTypes(): JsonResponse
    {
        $types = ContentType::latest()->get();

        return $this->success($types, 'Content types retrieved successfully');
    }

    /**
     * POST /api/v1/manage/infra/cck/types
     * Create a new dynamic content type schema.
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
            'fields.*.type' => 'required|string|in:'.implode(',', ContentTypeFieldRulesBuilder::allowedFieldTypes()),
            'fields.*.options' => 'nullable|array',
            'fields.*.options.*' => 'string',
            'fields.*.is_required' => 'nullable|boolean',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->error('Validasi Gagal', 422, $validator->errors()->toArray());
        }

        $payload = $validator->validated();

        $type = ContentType::create([
            'name' => $payload['name'],
            'slug' => $payload['slug'],
            'description' => $payload['description'] ?? null,
            'fields' => $payload['fields'],
            'is_active' => true,
        ]);

        return $this->success($type, 'Content type created successfully', 201);
    }

    /**
     * GET /api/v1/manage/infra/cck/types/{id}
     * Get a single content type schema by ID.
     */
    public function showType(string $id): JsonResponse
    {
        $type = ContentType::find($id);

        if (! $type) {
            return $this->error('Content type not found', 404);
        }

        return $this->success($type, 'Content type retrieved successfully');
    }

    /**
     * GET /api/v1/manage/infra/cck/types/by-slug/{slug}
     */
    public function showTypeBySlug(string $slug): JsonResponse
    {
        $type = ContentType::query()->where('slug', $slug)->first();

        if (! $type) {
            return $this->error('Content type not found', 404);
        }

        return $this->success($type, 'Content type retrieved successfully');
    }

    /**
     * GET /api/v1/manage/infra/cck/types/by-slug/{slug}/openapi
     * OpenAPI 3 document for /api/v1/dynamic/{slug} (field-driven).
     */
    public function openApiBySlug(string $slug, DynamicOpenApiBuilder $builder): JsonResponse
    {
        $type = ContentType::query()->where('slug', $slug)->where('is_active', true)->first();

        if (! $type) {
            return $this->error('Content type not found or inactive', 404);
        }

        return $this->success($builder->buildFor($type), 'OpenAPI document generated');
    }

    /**
     * GET /api/v1/manage/infra/cck/types/openapi-index
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
            'openapi_path' => '/api/v1/manage/infra/cck/types/by-slug/'.$type->slug.'/openapi',
            'dynamic_api_base' => '/api/v1/dynamic/'.$type->slug,
            'export_filename' => 'dynamic-'.$type->slug.'.openapi.json',
        ])->values()->all();

        return $this->success($index, 'Dynamic OpenAPI index');
    }

    /**
     * GET /api/v1/manage/infra/cck/types/{id}/validation-rules
     */
    public function typeValidationRules(string $id): JsonResponse
    {
        $type = ContentType::find($id);

        if (! $type) {
            return $this->error('Content type not found', 404);
        }

        return $this->success([
            'content_type_id' => $type->id,
            'slug' => $type->slug,
            'validation_rules' => ContentTypeFieldRulesBuilder::rulesFor($type),
            'field_types' => ContentTypeFieldRulesBuilder::allowedFieldTypes(),
        ], 'Validation rules resolved');
    }

    /**
     * PUT /api/v1/manage/infra/cck/types/{id}
     * Update an existing content type schema.
     */
    public function updateType(Request $request, string $id): JsonResponse
    {
        $type = ContentType::find($id);

        if (! $type) {
            return $this->error('Content type not found', 404);
        }

        $rules = [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:sys_content_types,slug,'.$id,
            'description' => 'nullable|string',
            'fields' => 'required|array',
            'fields.*.name' => 'required|string',
            'fields.*.slug' => 'required|string',
            'fields.*.type' => 'required|string|in:'.implode(',', ContentTypeFieldRulesBuilder::allowedFieldTypes()),
            'fields.*.options' => 'nullable|array',
            'fields.*.options.*' => 'string',
            'fields.*.is_required' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->error('Validasi Gagal', 422, $validator->errors()->toArray());
        }

        $payload = $validator->validated();

        $type->update([
            'name' => $payload['name'],
            'slug' => $payload['slug'],
            'description' => $payload['description'] ?? null,
            'fields' => $payload['fields'],
            'is_active' => isset($payload['is_active']) ? (bool) $payload['is_active'] : $type->is_active,
        ]);

        return $this->success($type, 'Content type updated successfully');
    }

    /**
     * DELETE /api/v1/manage/infra/cck/types/{id}
     * Delete an existing content type schema.
     */
    public function destroyType(string $id): JsonResponse
    {
        $type = ContentType::find($id);

        if (! $type) {
            return $this->error('Content type not found', 404);
        }

        $type->delete();

        return $this->success(null, 'Content type deleted successfully');
    }
}
