<?php

use App\Enums\LogLevels;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\IngredientSearchController;
use App\Http\Controllers\Api\RecipeController;
use Illuminate\Support\Facades\Route;
use Elastic\Elasticsearch\Client;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/es-test', function (Client $es) {
    $index = 'ingredients';

    // проверим доступность
    try {
        $ping = $es->ping();
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }

    // попробуем найти что-то (например, по имени)
    $result = $es->search([
        'index' => 'ingredients',
        'body'  => [
            'query' => [
                'match_phrase_prefix' => [
                    'name_ru' => 'майо'
                ]
            ],
            'size' => 10, // ограничение результатов
        ]
    ]);

    return response()->json([
        'status' => 'ok',
        'ping'   => $ping,
        'result' => $result->asArray(),
    ]);
});

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('/download/{path}', function ($path) {
    return \Illuminate\Support\Facades\Storage::disk('minio_files')->download($path);
})->where('path', '.*')->name('files.download');

Route::get('/ping', function () {
//    ClickHouseLog::log(LogLevels::WARNING, 'Что-то подозрительное', ['user_id' => 42]);

// // Читаем настройки из env
//     $config = [
//         'host' => env('CLICKHOUSE_HOST', 'localhost'),
//         'port' => env('CLICKHOUSE_PORT', 8123),
//         'username' => env('CLICKHOUSE_USER', 'default'),
//         'password' => env('CLICKHOUSE_PASSWORD', ''),
//         'database' => env('CLICKHOUSE_DATABASE', 'default'),
//     ];
//
//
//     // Создаем клиент ClickHouse
//     $client = new Client($config);
//
//     // Подключаемся к базе
//     $client->database($config['database']);
//
//     // Данные для вставки
//     $data = [
//         [
//             'timestamp' => date('Y-m-d H:i:s'),
//             'level' => 'info',
//             'message' => 'Test log from Laravel',
//             'context' => json_encode(['user' => 'tester']),
//             'extra' => '{}',
//         ]
//     ];
//
//     // Вставляем данные в таблицу logs
//     $client->insert('logs', $data);
//
//     return 'Record inserted into ClickHouse!';
});

Route::middleware('auth:api')->get('/pingAuth', function () {
    return response()->json([
        'message' => 'pong',
        'timestamp' => now()->toISOString()
    ]);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('postTest', [AuthController::class, 'postTest']);
    Route::post('getTest', [AuthController::class, 'getTest']);
});

Route::middleware('auth:api')->get('/auth/getSession', [AuthController::class, 'getSession']);

Route::get('/getBanners', [BannerController::class, 'index']);

Route::group([
    'middleware' => 'auth:api',
    'prefix' => 'recipes'
], function ($router) {
    Route::post('create-recipe', [RecipeController::class, 'createRecipe']);
    Route::post('update-recipe', [RecipeController::class, 'updateRecipe']);
    Route::delete('delete-recipe/{id}', [RecipeController::class, 'deleteRecipe']);
    Route::get('get-recipe-list', [RecipeController::class, 'getRecipeList']);
    Route::get('get-recipe-by-id/{id}', [RecipeController::class, 'getRecipeById']);
    Route::get('get-recipe-by-user-id/{id}', [RecipeController::class, 'getRecipeByUserId']);
    Route::get('/ingredients/search', IngredientSearchController::class);
});
