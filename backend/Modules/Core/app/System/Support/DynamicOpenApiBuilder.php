<?php

declare(strict_types=1);

namespace Modules\Core\System\Support;

use Modules\Core\System\Models\ContentType;

/**
 * Builds an OpenAPI 3.0 document for /api/v1/dynamic/{slug} from CCK field definitions.
 */
final class DynamicOpenApiBuilder
{
    /**
     * @return array<string, mixed>
     */
    public function buildFor(ContentType $type): array
    {
        $slug = $type->slug;
        $recordSchema = $this->recordSchema($type);
        $createSchema = $this->requestBodySchema($type);
        $envelope = $this->apiEnvelopeSchema($recordSchema);

        $paths = [
            "/api/v1/dynamic/{$slug}" => [
                'get' => [
                    'operationId' => "dynamic.{$slug}.index",
                    'summary' => "List {$type->name} records",
                    'tags' => ['Dynamic / '.$slug],
                    'parameters' => [
                        ['name' => 'search', 'in' => 'query', 'schema' => ['type' => 'string']],
                        ['name' => 'sort_by', 'in' => 'query', 'schema' => ['type' => 'string']],
                        ['name' => 'sort_order', 'in' => 'query', 'schema' => ['type' => 'string', 'enum' => ['asc', 'desc']]],
                        ['name' => 'per_page', 'in' => 'query', 'schema' => ['type' => 'integer', 'default' => 15]],
                        ['name' => 'page', 'in' => 'query', 'schema' => ['type' => 'integer', 'default' => 1]],
                    ],
                    'responses' => [
                        '200' => ['description' => 'Paginated records', 'content' => ['application/json' => ['schema' => $envelope]]],
                        '404' => ['description' => 'Unknown or inactive slug'],
                    ],
                ],
                'post' => [
                    'operationId' => "dynamic.{$slug}.store",
                    'summary' => "Create {$type->name} record",
                    'tags' => ['Dynamic / '.$slug],
                    'requestBody' => [
                        'required' => true,
                        'content' => ['application/json' => ['schema' => $createSchema]],
                    ],
                    'responses' => [
                        '201' => ['description' => 'Created', 'content' => ['application/json' => ['schema' => $envelope]]],
                        '422' => ['description' => 'Validation error'],
                        '404' => ['description' => 'Unknown or inactive slug'],
                    ],
                ],
            ],
            "/api/v1/dynamic/{$slug}/{id}" => [
                'get' => [
                    'operationId' => "dynamic.{$slug}.show",
                    'summary' => "Get {$type->name} record",
                    'tags' => ['Dynamic / '.$slug],
                    'parameters' => [
                        ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string', 'format' => 'uuid']],
                    ],
                    'responses' => [
                        '200' => ['description' => 'Record', 'content' => ['application/json' => ['schema' => $envelope]]],
                        '404' => ['description' => 'Not found'],
                    ],
                ],
                'put' => [
                    'operationId' => "dynamic.{$slug}.update",
                    'summary' => "Update {$type->name} record (partial merge)",
                    'tags' => ['Dynamic / '.$slug],
                    'parameters' => [
                        ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string', 'format' => 'uuid']],
                    ],
                    'requestBody' => [
                        'required' => true,
                        'content' => ['application/json' => ['schema' => $createSchema]],
                    ],
                    'responses' => [
                        '200' => ['description' => 'Updated', 'content' => ['application/json' => ['schema' => $envelope]]],
                        '422' => ['description' => 'Validation error'],
                        '404' => ['description' => 'Not found'],
                    ],
                ],
                'delete' => [
                    'operationId' => "dynamic.{$slug}.destroy",
                    'summary' => "Delete {$type->name} record",
                    'tags' => ['Dynamic / '.$slug],
                    'parameters' => [
                        ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string', 'format' => 'uuid']],
                    ],
                    'responses' => [
                        '200' => ['description' => 'Deleted'],
                        '404' => ['description' => 'Not found'],
                    ],
                ],
            ],
        ];

        return [
            'openapi' => '3.0.3',
            'info' => [
                'title' => "Jejakawan Dynamic API — {$type->name}",
                'version' => '1.0.0',
                'description' => $type->description ?? "EAV records for slug `{$slug}`",
            ],
            'servers' => [
                ['url' => url('/'), 'description' => 'Application root'],
            ],
            'tags' => [
                ['name' => 'Dynamic / '.$slug, 'description' => "Data model `{$slug}` (id: {$type->id})"],
            ],
            'paths' => $paths,
            'components' => [
                'schemas' => [
                    "{$slug}Record" => $recordSchema,
                    "{$slug}RecordInput" => $createSchema,
                    "{$slug}ApiEnvelope" => $envelope,
                ],
            ],
            'x-models' => [
                'content_type_id' => $type->id,
                'slug' => $slug,
                'fields' => $type->fields,
                'validation_rules' => DataModelFieldRulesBuilder::rulesFor($type),
            ],
            'x-cck' => [
                'content_type_id' => $type->id,
                'slug' => $slug,
                'fields' => $type->fields,
                'validation_rules' => DataModelFieldRulesBuilder::rulesFor($type),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function requestBodySchema(ContentType $type): array
    {
        $properties = [];
        $required = [];
        $fields = $type->fields;

        if (! is_array($fields)) {
            return ['type' => 'object', 'properties' => (object) []];
        }

        foreach ($fields as $field) {
            if (! is_array($field)) {
                continue;
            }
            $fieldSlug = is_scalar($field['slug'] ?? null) ? (string) $field['slug'] : '';
            if ($fieldSlug === '') {
                continue;
            }
            $properties[$fieldSlug] = $this->fieldOpenApiSchema($field);
            if ((bool) ($field['is_required'] ?? false)) {
                $required[] = $fieldSlug;
            }
        }

        $schema = ['type' => 'object', 'properties' => $properties];
        if ($required !== []) {
            $schema['required'] = $required;
        }

        return $schema;
    }

    /**
     * @return array<string, mixed>
     */
    private function recordSchema(ContentType $type): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'string', 'format' => 'uuid'],
                'content_type_id' => ['type' => 'string', 'format' => 'uuid'],
                'data' => $this->requestBodySchema($type),
                'created_at' => ['type' => 'string', 'format' => 'date-time'],
                'updated_at' => ['type' => 'string', 'format' => 'date-time'],
            ],
            'required' => ['id', 'content_type_id', 'data'],
        ];
    }

    /**
     * @param  array<string, mixed>  $envelopeDataSchema
     * @return array<string, mixed>
     */
    private function apiEnvelopeSchema(array $envelopeDataSchema): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'success' => ['type' => 'boolean', 'example' => true],
                'message' => ['type' => 'string'],
                'data' => $envelopeDataSchema,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $field
     * @return array<string, mixed>
     */
    private function fieldOpenApiSchema(array $field): array
    {
        $typeVal = $field['type'] ?? 'text';
        $fieldType = is_scalar($typeVal) ? (string) $typeVal : 'text';

        return match ($fieldType) {
            'number' => ['type' => 'number'],
            'boolean' => ['type' => 'boolean'],
            'date' => ['type' => 'string', 'format' => 'date'],
            'email' => ['type' => 'string', 'format' => 'email'],
            'url' => ['type' => 'string', 'format' => 'uri'],
            'color' => ['type' => 'string', 'example' => '#3b82f6', 'description' => 'Hex color code'],
            'image' => ['type' => 'string', 'format' => 'uri', 'description' => 'Image URL or path'],
            'media' => ['type' => 'string', 'description' => 'Media asset path or UUID'],
            'richtext' => ['type' => 'string', 'description' => 'Formatted rich HTML or Markdown text'],
            'json' => ['type' => 'object', 'description' => 'Nested structured JSON / key-value dictionary'],
            'relation' => $this->relationSchema($field),
            'select' => $this->selectSchema($field),
            'longtext' => ['type' => 'string'],
            default => ['type' => 'string'],
        };
    }

    /**
     * @param  array<string, mixed>  $field
     * @return array<string, mixed>
     */
    private function relationSchema(array $field): array
    {
        $targetType = is_scalar($field['target_type'] ?? null) ? (string) $field['target_type'] : 'record';
        $relationMode = is_scalar($field['relation_mode'] ?? null) ? (string) $field['relation_mode'] : 'single';

        if ($relationMode === 'multiple') {
            return [
                'type' => 'array',
                'description' => "List of referenced IDs from `{$targetType}`",
                'items' => ['type' => 'string', 'format' => 'uuid'],
            ];
        }

        return [
            'type' => 'string',
            'format' => 'uuid',
            'description' => "Referenced record ID from `{$targetType}`",
        ];
    }

    /**
     * @param  array<string, mixed>  $field
     * @return array<string, mixed>
     */
    private function selectSchema(array $field): array
    {
        $schema = ['type' => 'string'];
        $options = $field['options'] ?? [];
        if (is_array($options) && $options !== []) {
            $enum = [];
            foreach ($options as $option) {
                if (is_scalar($option)) {
                    $enum[] = (string) $option;
                }
            }
            if ($enum !== []) {
                $schema['enum'] = $enum;
            }
        }

        return $schema;
    }
}
