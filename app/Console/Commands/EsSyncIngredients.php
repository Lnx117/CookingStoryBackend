<?php

namespace App\Console\Commands;

use App\Models\Ingredient;
use Elastic\Elasticsearch\Client;
use Illuminate\Console\Command;

class EsSyncIngredients extends Command
{
    protected $signature = 'es:ingredients-sync {--chunk=1000}';
    protected $description = 'Bulk reindex ingredients into Elasticsearch';

    public function handle(Client $es)
    {
        $index = 'ingredients';
        $chunk = (int)$this->option('chunk');

        Ingredient::query()->orderBy('id')->chunk($chunk, function ($items) use ($es, $index) {
            $body = [];
            foreach ($items as $ing) {
                $doc = [
                    'id'        => (int)$ing->id,
                    'name_ru'   => (string)$ing->name_ru,
                    'slug'      => (string)($ing->slug ?? ''),
                    'calories'  => $ing->calories ? (int)$ing->calories : null,
                    'proteins'  => $ing->proteins !== null ? (float)$ing->proteins : null,
                    'fats'      => $ing->fats !== null ? (float)$ing->fats : null,
                    'carbs'     => $ing->carbs !== null ? (float)$ing->carbs : null,
                    'created_at'=> optional($ing->created_at)->toAtomString(),
                    'updated_at'=> optional($ing->updated_at)->toAtomString(),
                ];

                $body[] = ['index' => ['_index' => $index, '_id' => $ing->id]];
                $body[] = $doc;
            }

            if ($body) {
                $es->bulk(['body' => $body]);
                $this->info("Indexed ".count($items)." items...");
            }
        });

        $this->info("Sync complete.");
        return self::SUCCESS;
    }
}
