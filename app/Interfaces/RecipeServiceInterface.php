<?php
namespace App\Interfaces;

use App\Models\Recipe;
use App\Models\User;

interface RecipeServiceInterface
{
    public function createRecipe(array $data, User $user): Recipe;
    public function updateRecipe(Recipe $recipe, array $data): Recipe;
    public function deleteRecipe(Recipe $recipe): bool;

//    public function orchidUpdateBanner(BannerUpdateRequest $request): bool;
//    public function orchidDeleteBanner(Request $request): bool;
//    public function getBanners(array $request): array;

}
