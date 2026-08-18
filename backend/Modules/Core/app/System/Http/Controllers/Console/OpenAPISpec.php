<?php

declare(strict_types=1);

namespace Modules\Core\System\Http\Controllers\Console;

/**
 * @OA\Info(
 *     title="Jejakawan API Documentation",
 *     version="1.0.0",
 *     description="Comprehensive API documentation for Jejakawan Content Management System",
 *
 *     @OA\Contact(
 *         email="support@jejakawan.com"
 *     ),
 *
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Laravel Sanctum Authentication"
 * )
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="User authentication endpoints"
 * )
 * @OA\Tag(
 *     name="Content",
 *     description="Content management endpoints"
 * )
 * @OA\Tag(
 *     name="Media",
 *     description="Media management endpoints"
 * )
 * @OA\Tag(
 *     name="Categories",
 *     description="Category management endpoints"
 * )
 * @OA\Tag(
 *     name="Tags",
 *     description="Tag management endpoints"
 * )
 * @OA\Tag(
 *     name="Users",
 *     description="User management endpoints"
 * )
 * @OA\Tag(
 *     name="Settings",
 *     description="System settings endpoints"
 * )
 *
 * @OA\Schema(
 *     schema="SuccessResponse",
 *     type="object",
 *
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Operation successful"),
 *     @OA\Property(property="data", type="object")
 * )
 *
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="An error occurred"),
 *     @OA\Property(property="error_code", type="string", example="ERROR"),
 *     @OA\Property(property="trace_id", type="string", example="err_1234567890"),
 *     @OA\Property(property="errors", type="object")
 * )
 *
 * @OA\Schema(
 *     schema="ValidationErrorResponse",
 *     type="object",
 *
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Validation failed"),
 *     @OA\Property(property="error_code", type="string", example="VALIDATION_ERROR"),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         @OA\Property(
 *             property="field_name",
 *             type="array",
 *
 *             @OA\Items(type="string", example="The field name is required.")
 *         )
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="PaginatedResponse",
 *     type="object",
 *
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Data retrieved successfully"),
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         @OA\Property(property="data", type="array", @OA\Items(type="object")),
 *         @OA\Property(
 *             property="pagination",
 *             type="object",
 *             @OA\Property(property="current_page", type="integer", example=1),
 *             @OA\Property(property="per_page", type="integer", example=15),
 *             @OA\Property(property="total", type="integer", example=100),
 *             @OA\Property(property="last_page", type="integer", example=7),
 *             @OA\Property(property="from", type="integer", example=1),
 *             @OA\Property(property="to", type="integer", example=15)
 *         )
 *     )
 * )
 */
class OpenAPISpec
{
    // This class serves as a container for OpenAPI annotations
}
