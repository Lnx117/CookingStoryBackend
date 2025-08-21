<?php

namespace App\Interfaces;

use App\Models\Recipe;
use App\Models\Tag;

interface RecipeTagRepositoryInterface
{
    public function attach(Recipe $recipe, array $tags): void;

    public function sync(Recipe $recipe, array $tags): void;

    public function detach(Recipe $recipe): void;
}
