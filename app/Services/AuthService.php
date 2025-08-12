<?php
namespace App\Services;


use App\Enums\LogLevels;
use App\Facades\ClickHouseLog;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Interfaces\AuthServiceInterface;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService implements AuthServiceInterface
{

    public function register($request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:30','regex:/^[a-zA-Zа-яА-ЯёЁ\s\-\']+$/u'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:6', 'max:30'],
                'confirmPassword' => ['required', 'string', 'same:password'],
            ]);

            if ($validator->fails()) {
                ClickHouseLog::log(LogLevels::WARNING, 'Ошибка валидации при регистрации', ['Email' => $request->email, 'Errors' => $validator->errors()]);
                return ApiResponse::error(
                    'Ошибка валидации',
                    422,
                    $validator->errors()
                );
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $token = JWTAuth::fromUser($user);

            ClickHouseLog::log(LogLevels::INFO, 'Удачная регистрация пользователя', ['Email' => $request->email]);

            return ApiResponse::success(
                [
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => config('jwt.ttl') * 60,
                    'user' => $user,
                ],
                'Регистрация прошла успешно'
            );
        } catch (\Throwable $e) {
            ClickHouseLog::log(LogLevels::ERROR, 'Неудачная попытка регистрации', ['Email' => $request->email, 'Error' => $e->getMessage()]);
            return ApiResponse::error(
                'Неизвестная ошибка',
                500,
                [$e->getMessage()]
            );
        }
    }

    public function login($request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:6,max:30',
            ]);

            if ($validator->fails()) {
                ClickHouseLog::log(LogLevels::WARNING, 'Ошибка валидации при авторизации', ['Email' => $request->email, 'Errors' => $validator->errors()]);
                return ApiResponse::error(
                    'Ошибка валидации',
                    422,
                    $validator->errors()
                );
            }

            $credentials = $request->only('email', 'password');

            if (!$token = auth('api')->attempt($credentials)) {
                return ApiResponse::error(
                    'Неверные учетные данные',
                    401,
                    [],
                );
            }

            $user = auth('api')->user();

            ClickHouseLog::log(LogLevels::INFO, 'Удачная авторизация пользователя', ['Email' => $request->email]);

            return ApiResponse::success([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 'Авторизация прошла успешно');

        } catch (\Throwable $e) {
            ClickHouseLog::log(LogLevels::ERROR, 'Неудачная попытка авторизации', ['Email' => $request->email, 'Error' => $e->getMessage()]);
            return ApiResponse::error(
                'Неизвестная ошибка',
                500,
                [$e->getMessage()]
            );
        }
    }

    public function logout($request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                ClickHouseLog::log(LogLevels::ERROR, 'Неудачная попытка logout', ['Request' => $request]);
                return ApiResponse::error(
                    'Пользователь не авторизован',
                    401,
                    [],
                );
            }

            $token = $user->currentAccessToken();

            $token->delete();

            ClickHouseLog::log(LogLevels::INFO, 'Пользователь вышел logout', ['user' => $user]);
            return ApiResponse::success([
                'user' => $user,
            ], 'Logout пользователя');

        } catch (\Throwable $e) {
            return response()->json(['error' => 'Logout failed: ' . $e->getMessage()], 500);
        }
    }

    public function getSession($request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                ClickHouseLog::log(LogLevels::ERROR, 'Неудачная попытка получения данных пользователя GetSession', ['Request' => $request]);
                return ApiResponse::error(
                    'Пользователь не найден (не авторизован)',
                    401,
                    []
                );
            }

            return response()->json($user);

        } catch (\Throwable $e) {
            ClickHouseLog::log(LogLevels::ERROR, 'Неудачная попытка получения данных пользователя GetSession', ['Request' => $request, 'Error' => $e->getMessage()]);
            return ApiResponse::error(
                'Неизвестная ошибка',
                500,
                [$e->getMessage()]
            );
        }
    }
}
