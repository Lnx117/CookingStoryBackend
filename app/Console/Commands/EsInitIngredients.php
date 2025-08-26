<?php

namespace App\Console\Commands;

use Elastic\Elasticsearch\Client;
use Illuminate\Console\Command;

class EsInitIngredients extends Command
{
    protected $signature = 'es:ingredients-init {--force : recreate index}';
    protected $description = 'Create/recreate ES index for ingredients';

    public function handle(Client $es)
    {
        $index = 'ingredients';

        if ($es->indices()->exists(['index' => $index])->asBool()) {
            if (!$this->option('force')) {
                $this->info("Index [$index] already exists.");
                return self::SUCCESS;
            }
            $this->warn("Deleting index [$index]...");
            $es->indices()->delete(['index' => $index]);
        }

        $this->info("Creating index [$index]...");
        $es->indices()->create([
            'index' => $index,
            'body' => [
                'settings' => [
                    'analysis' => [
                        'normalizer' => [
                            'lowercase_normalizer' => [
                                'type' => 'custom',
                                'char_filter' => [],
                                'filter' => ['lowercase','asciifolding']
                            ]
                        ]
                    ]
                ],
                'mappings' => [
                    'properties' => [
                        'id'        => ['type' => 'integer'],
                        'name_ru'   => ['type' => 'search_as_you_type'],
                        'slug'      => ['type' => 'keyword', 'normalizer' => 'lowercase_normalizer'],
                        'calories'  => ['type' => 'integer'],
                        'proteins'  => ['type' => 'scaled_float', 'scaling_factor' => 100],
                        'fats'      => ['type' => 'scaled_float', 'scaling_factor' => 100],
                        'carbs'     => ['type' => 'scaled_float', 'scaling_factor' => 100],
                        'created_at'=> ['type' => 'date'],
                        'updated_at'=> ['type' => 'date'],
                    ]
                ]
            ],
        ]);

        $this->info('Done.');
        return self::SUCCESS;
    }
}
