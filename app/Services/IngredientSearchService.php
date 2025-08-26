<?php

namespace App\Services;

use App\Interfaces\IngredientSearchServiceInterface;
use Elastic\Elasticsearch\Client;

class IngredientSearchService implements IngredientSearchServiceInterface
{
    public function __construct(
        private Client $es
    ) {}

    public function search(string $query, int $limit = 10): array
    {
        if (trim($query) === '') {
            return [];
        }

        $result = $this->es->search([
            'index' => 'ingredients',
            'body'  => [
                'query' => [
                    'multi_match' => [
                        'query'  => $query,
                        'type'   => 'bool_prefix',
                        'fields' => [
                            'name_ru',
                            'name_ru._2gram',
                            'name_ru._3gram'
                        ]
                    ]
                ],
                'size' => $limit,
            ]
        ]);

        $hits = $result->asArray()['hits']['hits'] ?? [];

        return collect($hits)->map(fn ($hit) => [
            'id'   => $hit['_source']['id'] ?? null,
            'name' => $hit['_source']['name_ru'] ?? null,
        ])->all();
    }
}
