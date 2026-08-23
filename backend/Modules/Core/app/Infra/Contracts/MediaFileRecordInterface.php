<?php

declare(strict_types=1);

namespace Modules\Core\Infra\Contracts;

/**
 * Optional downstream media record (e.g. Media pack File model).
 *
 * Downstream apps should implement this on their media File Eloquent model.
 * Method signatures intentionally match Eloquent Model (no conflicting return types).
 */
interface MediaFileRecordInterface
{
    public function getId(): int|string|null;

    public function getPath(): string;

    public function setPath(string $path): void;

    /**
     * @param  array<string, mixed>  $options
     * @return bool
     */
    public function save(array $options = []);

    /**
     * @return bool|null
     */
    public function delete();

    /**
     * @return bool
     */
    public function restore();
}
