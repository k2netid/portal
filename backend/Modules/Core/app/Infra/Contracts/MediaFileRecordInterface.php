<?php

declare(strict_types=1);

namespace Modules\Core\Infra\Contracts;

/**
 * Optional downstream media record (e.g. Content module File model).
 *
 * Downstream apps should implement this on their media File Eloquent model.
 */
interface MediaFileRecordInterface
{
    public function getId(): int|string|null;

    public function getPath(): string;

    public function setPath(string $path): void;

    public function save(): bool;

    public function delete(): bool|null;

    public function restore(): bool;
}
