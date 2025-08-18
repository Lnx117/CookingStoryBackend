<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ingredient;
use League\Csv\Reader;
use Illuminate\Support\Str;

class IngredientSeeder extends Seeder
{
    public function run(): void
    {
        $csv = Reader::createFromPath(database_path('seeders/data/ingredients.csv'), 'r');
        $csv->setDelimiter(';'); //разделитель
        $csv->setHeaderOffset(0);

        foreach ($csv as $record) {
            Ingredient::create([
                'name_ru'   => trim($record['Продукт']),
                'slug'      => Str::slug($record['Продукт']),
                'proteins'  => (float) str_replace(',', '.', $record['Белки (г)']),
                'fats'      => (float) str_replace(',', '.', $record['Жиры (г)']),
                'carbs'     => (float) str_replace(',', '.', $record['Углеводы (г)']),
                'calories'  => (int) $record['Калории (ккал)'],
            ]);
        }
    }
}
