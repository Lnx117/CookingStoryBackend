<?php

namespace App\Interfaces;

interface IngredientSearchServiceInterface
{
    /**
     * Поиск ингредиентов по части слова
     *
     * @param string $query
     * @param int $limit
     * @return array{id:int,name:string}[]
     */
    public function search(string $query, int $limit = 10): array;
}
