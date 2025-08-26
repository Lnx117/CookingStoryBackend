<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ru',
        'slug',
        'calories',
        'proteins',
        'fats',
        'carbs',
    ];

    protected static function booted()
    {
        static::saved(function (Ingredient $ing) {
            dispatch(function () use ($ing) {
                app(\Elastic\Elasticsearch\Client::class)->index([
                    'index' => 'ingredients',
                    'id'    => $ing->id,
                    'body'  => [
                        'id'        => (int)$ing->id,
                        'name_ru'   => (string)$ing->name_ru,
                        'slug'      => (string)($ing->slug ?? ''),
                        'calories'  => $ing->calories ? (int)$ing->calories : null,
                        'proteins'  => $ing->proteins !== null ? (float)$ing->proteins : null,
                        'fats'      => $ing->fats !== null ? (float)$ing->fats : null,
                        'carbs'     => $ing->carbs !== null ? (float)$ing->carbs : null,
                        'created_at'=> optional($ing->created_at)->toAtomString(),
                        'updated_at'=> optional($ing->updated_at)->toAtomString(),
                    ]
                ]);
            })->onQueue('default');
        });

        static::deleted(function (Ingredient $ing) {
            dispatch(function () use ($ing) {
                app(\Elastic\Elasticsearch\Client::class)->delete([
                    'index' => 'ingredients',
                    'id'    => $ing->id,
                    'ignore' => 404,
                ]);
            })->onQueue('default');
        });
    }

    public function recipes()
    {
        return $this->belongsToMany(Recipe::class, 'recipe_ingredients')
            ->withPivot('weight_grams')
            ->withTimestamps();
    }
}
