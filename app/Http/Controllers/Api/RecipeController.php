<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Interfaces\RecipeServiceInterface;
use App\Models\Recipe;

class RecipeController extends Controller {

    protected RecipeServiceInterface $recipe;

    public function __construct(RecipeServiceInterface $recipe) {
        $this->recipe = $recipe;
    }

    public function index() {
        $recipes = Recipe::all();
    }

    public function createRecipe() {
        $recipes = Recipe::all();
    }

    public function getRecipe() {
        $recipes = Recipe::all();
    }

    public function deleteRecipe() {
        $recipes = Recipe::all();
    }

    public function updateRecipe() {
        $recipes = Recipe::all();
    }
}
