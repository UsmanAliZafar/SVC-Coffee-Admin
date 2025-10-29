<?php

namespace App\Http\Controllers\Api;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="SVC Ecommerce API",
 *     description="API documentation for Coffee Ecommerce Platform",
 *     @OA\Contact(
 *         email="support@coffeeecommerce.com"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Local Development Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="apiKey",
 *     type="apiKey",
 *     in="header",
 *     name="X-API-Key",
 *     description="API Key Authentication"
 * )
 *
 * @OA\Tag(
 *     name="Public",
 *     description="Public endpoints (no authentication)"
 * )
 *
 * @OA\Tag(
 *     name="Categories",
 *     description="Category management"
 * )
 *
 * @OA\Tag(
 *     name="Products",
 *     description="Product management"
 * )
 */


class SwaggerAnnotations
{
    // This class is just for Swagger annotations
}
