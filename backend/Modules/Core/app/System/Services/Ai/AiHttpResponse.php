<?php

declare(strict_types=1);

namespace Modules\Core\System\Services\Ai;

use Illuminate\Http\Client\Response;

final class AiHttpResponse
{
    /**
     * @return array<string, mixed>
     */
    public static function jsonArray(Response $response): array
    {
        $data = $response->json();

        return is_array($data) ? $data : [];
    }

    /**
     * @param  array<string, mixed>|null  $data
     */
    public static function chatCompletionContent(?array $data): string
    {
        if ($data === null) {
            return '';
        }

        $choices = $data['choices'] ?? null;
        if (! is_array($choices) || ! isset($choices[0]) || ! is_array($choices[0])) {
            return '';
        }

        $message = $choices[0]['message'] ?? null;
        if (! is_array($message)) {
            return '';
        }

        $content = $message['content'] ?? '';

        return is_string($content) ? $content : '';
    }

    /**
     * @param  array<string, mixed>|null  $data
     */
    public static function geminiTextContent(?array $data): string
    {
        if ($data === null) {
            return '';
        }

        $candidates = $data['candidates'] ?? null;
        if (! is_array($candidates) || ! isset($candidates[0]) || ! is_array($candidates[0])) {
            return '';
        }

        $content = $candidates[0]['content'] ?? null;
        if (! is_array($content)) {
            return '';
        }

        $parts = $content['parts'] ?? null;
        if (! is_array($parts) || ! isset($parts[0]) || ! is_array($parts[0])) {
            return '';
        }

        $text = $parts[0]['text'] ?? '';

        return is_string($text) ? $text : '';
    }

    /**
     * @param  array<string, mixed>|null  $data
     */
    public static function claudeTextContent(?array $data): string
    {
        if ($data === null) {
            return '';
        }

        $contentBlocks = $data['content'] ?? null;
        if (! is_array($contentBlocks) || ! isset($contentBlocks[0]) || ! is_array($contentBlocks[0])) {
            return '';
        }

        $text = $contentBlocks[0]['text'] ?? '';

        return is_string($text) ? $text : '';
    }

    public static function errorMessage(Response $response): string
    {
        $errorPayload = $response->json('error');
        if (is_array($errorPayload) && isset($errorPayload['message']) && is_string($errorPayload['message'])) {
            return $errorPayload['message'];
        }

        $body = $response->body();

        return is_string($body) ? $body : '';
    }
}
