<?php
namespace App\Interfaces;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

interface AuthServiceInterface
{
    public function register(Request $request): JsonResponse;

    public function login(Request $request): JsonResponse;

    public function logout(Request $request): JsonResponse;

    public function getSession(Request $request): JsonResponse;

}
