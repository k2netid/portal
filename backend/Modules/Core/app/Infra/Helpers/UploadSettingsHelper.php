<?php

namespace Modules\Core\Infra\Helpers;

use Modules\Core\System\Models\Setting;

class UploadSettingsHelper
{
    /**
     * Get maximum upload size in kilobytes
     */
    public static function getMaxUploadSize(): int
    {
        $raw = Setting::get('max_upload_size', 5120);

        return is_numeric($raw) ? (int) $raw : 5120;
    }

    /**
     * Get allowed file extensions
     *
     * @return string[]
     */
    public static function getAllowedExtensions(): array
    {
        $extensions = Setting::get('allowed_upload_extensions', 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,txt,zip');
        $csv = is_scalar($extensions) ? (string) $extensions : 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,txt,zip';

        return array_values(array_filter(array_map(
            static fn (string $ext): string => strtolower(trim($ext)),
            explode(',', $csv)
        )));
    }

    public static function isExtensionAllowed(string $extension): bool
    {
        $extension = strtolower(trim($extension));

        return $extension !== '' && in_array($extension, self::getAllowedExtensions(), true);
    }

    /**
     * Get allowed image types
     *
     * @return string[]
     */
    public static function getAllowedImageTypes(): array
    {
        $types = Setting::get('allowed_image_types', 'jpg,jpeg,png,gif,webp');
        $csv = is_string($types) ? $types : 'jpg,jpeg,png,gif,webp';

        return array_values(array_filter(array_map(
            static fn (string $t): string => strtolower(trim($t)),
            explode(',', $csv)
        )));
    }

    /**
     * Get validation rules for file upload
     *
     * @return array<string, array<int, string>>
     */
    public static function getUploadValidationRules(): array
    {
        $maxSize = self::getMaxUploadSize();
        $allowedExtensions = implode(',', self::getAllowedExtensions());

        return [
            'file' => [
                'required',
                'file',
                'max:'.$maxSize,
                'mimes:'.$allowedExtensions,
            ],
        ];
    }

    /**
     * Get validation rules for image upload only
     *
     * @return array<string, array<int, string>>
     */
    public static function getImageUploadValidationRules(): array
    {
        $maxSize = self::getMaxUploadSize();
        $allowedImages = implode(',', self::getAllowedImageTypes());

        return [
            'file' => [
                'required',
                'file',
                'max:'.$maxSize,
                'mimes:'.$allowedImages,
            ],
        ];
    }
}
