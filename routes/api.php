<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Enums\LogLevels;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('/ping', function () {
    ClickHouseLog::log(LogLevels::WARNING, 'Что-то подозрительное', ['user_id' => 42]);

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
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('postTest', [AuthController::class, 'postTest']);
    Route::get('getTest', [AuthController::class, 'getTest']);
    // Route::get('getSession', [AuthController::class, 'getUser']);
});


Route::middleware('auth:api')->get('/auth/getSession', [AuthController::class, 'getUser']);
