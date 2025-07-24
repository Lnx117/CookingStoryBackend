<?php

namespace App\Documentation;

/**
 * @OA\Info(
 *     title="My API",
 *     version="1.0.0",
 *     description="API documentation",
 *     @OA\Contact(
 *         email="support@example.com"
 *     ),
 *     @OA\License(
 *         name="Apache 2.0",
 *         url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Local API Server"
 * )
 * 
 * @OA\Tag(
 *     name="Authentication",
 *     description="API Endpoints for Authentication"
 * )
 * 
 * @OA\Tag(
 *     name="Products",
 *     description="API Endpoints for Products"
 * )
 */
class ApiInfo {}