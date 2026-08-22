<?php

declare(strict_types=1);

namespace Modules\Core\System\Contracts;

interface AiProviderInterface
{
    public function getName(): string;

    public function generateText(string $prompt, string $context = '', string $model = ''): string;

    /**
     * @return array<int, array{id: string, name: string}>
     */
    public function getModels(): array;

    public function testConnection(): bool;
}
