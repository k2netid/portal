<?php

declare(strict_types=1);

namespace Modules\Core\System\Support;

use Modules\Core\System\Models\ContentType;

/**
 * Builds Laravel validation rules from Data Model field definitions.
 */
class DataModelFieldRulesBuilder
{
    /**
     * @return array<int, string>
     */
    public static function allowedFieldTypes(): array
    {
        return [
            'text',
            'longtext',
            'richtext',
            'number',
            'boolean',
            'date',
            'image',
            'media',
            'email',
            'url',
            'color',
            'select',
            'relation',
            'json',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function rulesFor(ContentType $contentType): array
    {
        $rules = [];
        $fields = $contentType->fields;

        if (! is_array($fields)) {
            return $rules;
        }

        foreach ($fields as $field) {
            if (! is_array($field)) {
                continue;
            }

            $slugVal = $field['slug'] ?? '';
            $fieldSlug = is_scalar($slugVal) ? (string) $slugVal : '';
            if ($fieldSlug === '') {
                continue;
            }

            $typeVal = $field['type'] ?? 'text';
            $fieldType = is_scalar($typeVal) ? (string) $typeVal : 'text';
            $isRequired = (bool) ($field['is_required'] ?? false);

            $ruleList = [];
            $ruleList[] = $isRequired ? 'required' : 'nullable';

            switch ($fieldType) {
                case 'number':
                    $ruleList[] = 'numeric';
                    break;
                case 'boolean':
                    $ruleList[] = 'boolean';
                    break;
                case 'date':
                    $ruleList[] = 'date';
                    break;
                case 'email':
                    $ruleList[] = 'email';
                    break;
                case 'url':
                    $ruleList[] = 'url';
                    break;
                case 'color':
                    $ruleList[] = 'string';
                    $ruleList[] = 'regex:/^#([A-Fa-f0-9]{3,8})$/';
                    break;
                case 'json':
                    $ruleList[] = 'array';
                    break;
                case 'relation':
                    $relationMode = $field['relation_mode'] ?? 'single';
                    if ($relationMode === 'multiple') {
                        $ruleList[] = 'array';
                    } else {
                        $ruleList[] = 'string';
                    }
                    break;
                case 'select':
                    $ruleList[] = 'string';
                    $options = $field['options'] ?? [];
                    if (is_array($options) && $options !== []) {
                        $allowed = [];
                        foreach ($options as $option) {
                            if (is_scalar($option)) {
                                $allowed[] = (string) $option;
                            }
                        }
                        if ($allowed !== []) {
                            $ruleList[] = 'in:'.implode(',', $allowed);
                        }
                    }
                    break;
                case 'media':
                case 'image':
                case 'richtext':
                case 'longtext':
                case 'text':
                default:
                    $ruleList[] = 'string';
                    break;
            }

            $rules[$fieldSlug] = implode('|', $ruleList);
        }

        return $rules;
    }
}
