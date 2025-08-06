<?php

namespace App\Http\Controllers\Rest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;
use App\Firstbit\{Response, ResponseCodes};

class BannerController extends Controller
{
    /**
     * Метод для получения данных баннера главной страницы
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {


        try {
            $user = auth('api')->user();
            $banners = Banner::where('active', true)->with('program')->get()->map(function ($banner) use ($user) {
                return [
                    'program' => $banner->program ?? null,
                    'url' => $banner->url ?? '',
                    'title' => $banner->title ?? '',
                    'short_description' => $banner->short_description ?? '',
                    'cart' => $user && (bool) $banner->program->carts
                            ->where('user_id', $user->id)
                            ->where('program_id', $banner->program->id)
                            ->first(),
                    'favourite' => $user && (bool) $banner->program->favorites
                            ->where('user_id', $user->id)
                            ->where('program_id', $banner->program->id)
                            ->first(),
                ];
            });

            return (new Response(
                ResponseCodes::SUCCESS, $banners->toArray(), __('phrase.successfully_registered')
            ))->getResponse();

        } catch (\Exception $e) {
            return (new Response(
                ResponseCodes::WARNING, [$e->getMessage()], __('phrase.successfully_registered')
            ))->getResponse();
        }
    }

}
