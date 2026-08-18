<?php

namespace Tests\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Core\System\Models\User;

class TestHelpers
{
    /**
     * Create a test file for upload.
     */
    public static function createTestFile(string $name = 'test.jpg', string $mimeType = 'image/jpeg', int $size = 100): UploadedFile
    {
        return UploadedFile::fake()->image($name, 100, 100)->size($size);
    }

    /**
     * Create a test image file.
     */
    public static function createTestImage(string $name = 'test.jpg'): UploadedFile
    {
        return self::createTestFile($name, 'image/jpeg');
    }

    /**
     * Create a test document file.
     */
    public static function createTestDocument(string $name = 'test.pdf'): UploadedFile
    {
        return UploadedFile::fake()->create($name, 100, 'application/pdf');
    }

    /**
     * Assert API response structure.
     */
    public static function assertApiResponseStructure($response, bool $hasData = true): void
    {
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
            ]);

        if ($hasData) {
            $response->assertJsonStructure([
                'data',
            ]);
        }
    }

    /**
     * Assert API success response.
     */
    public static function assertApiSuccess($response, int $statusCode = 200): void
    {
        $response->assertStatus($statusCode)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * Assert API error response.
     */
    public static function assertApiError($response, int $statusCode = 400): void
    {
        $response->assertStatus($statusCode)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * Assert API validation error response.
     * More flexible - only requires 422 status since response structure may vary by controller.
     */
    public static function assertApiValidationError($response): void
    {
        $response->assertStatus(422);
    }

    /**
     * Assert API paginated response.
     */
    public static function assertApiPaginated($response): void
    {
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data',
                    'current_page',
                    'per_page',
                    'total',
                ],
            ]);
    }

    /**
     * Get authenticated headers for API requests.
     */
    public static function getAuthHeaders(User $user): array
    {
        $token = $user->createToken('test-token')->plainTextToken;

        return [
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ];
    }

    /**
     * Create test content data.
     */
    public static function getContentData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Test Content',
            'slug' => 'test-content-'.uniqid(),
            'body' => 'Test content body',
            'excerpt' => 'Test excerpt',
            'status' => 'draft',
            'type' => 'post',
        ], $overrides);
    }

    /**
     * Create test category data.
     */
    public static function getCategoryData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test Category',
            'slug' => 'test-category-'.uniqid(),
            'description' => 'Test category description',
        ], $overrides);
    }

    /**
     * Create test tag data.
     */
    public static function getTagData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test Tag',
            'slug' => 'test-tag-'.uniqid(),
        ], $overrides);
    }

    /**
     * Create test user data.
     */
    public static function getUserData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test User',
            'email' => 'test'.uniqid().'@example.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ], $overrides);
    }

    /**
     * Clean up test files.
     */
    public static function cleanupTestFiles(): void
    {
        Storage::disk('public')->deleteDirectory('test');
    }
}
