<?php
namespace App\Repositories;

use App\Http\Requests\BannerUpdateRequest;
use App\Interfaces\RecipeRepositoryInterface;
use App\Models\Banner;
use App\Interfaces\BannerRepositoryInterface;
use App\Interfaces\FileStoreServiceInterface;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Orchid\Attachment\Models\Attachment;

class RecipeRepository implements RecipeRepositoryInterface
{
    protected Recipe $model;
    protected FileStoreServiceInterface $fileStoreService;

    public function __construct(Recipe $banner, FileStoreServiceInterface $fileStoreService) {
        $this->model = $banner;
        $this->fileStoreService = $fileStoreService;
    }

    public function create(array $data): Recipe
    {
        // Если slug не передали или он пустой
        if (empty($data['slug']) && !empty($data['title'])) {
            $data['slug'] = $this->makeUniqueSlug($data['title']);
        }

        // Даже если slug передали, проверим уникальность
        if (!empty($data['slug']) && $this->existsSlug($data['slug'])) {
            $data['slug'] = $this->makeUniqueSlug($data['slug'], false);
        }

        $recipe = Recipe::create($data);

        return $recipe->refresh();
    }

    public function update(Recipe $recipe, array $data): Recipe
    {
        // Если явно передали slug т проверим уникальность
        if (!empty($data['slug'])) {
            $data['slug'] = $this->makeUniqueSlug($data['slug'], true, $recipe->id);
        }

        $recipe->fill($data)->save();

        return $recipe->refresh();
    }

    public function delete(Recipe $recipe): bool
    {
        return (bool) $recipe->delete();
    }

    public function findById(int $id, array $with = []): ?Recipe
    {
        return Recipe::with($with)->find($id);
    }
//
//    public function findBySlug(string $slug, array $with = []): ?Recipe
//    {
//        return Recipe::with($with)->where('slug', $slug)->first();
//    }
//
//
    public function paginate(array $filters = [], int $perPage = 15, array $with = []): LengthAwarePaginator
    {
        $q = Recipe::query()->with($with);

        if (!empty($filters['is_published'])) {
            $q->where('is_published', (bool) $filters['is_published']);
        }

        if (!empty($filters['author_id'])) {
            $q->where('user_id', (int) $filters['author_id']);
        }

        if (!empty($filters['tag_ids']) && is_array($filters['tag_ids'])) {
            $q->whereHas('tags', fn (Builder $b) => $b->whereIn('tags.id', $filters['tag_ids']));
        }

        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $q->where(function (Builder $b) use ($term) {
                $b->where('title', 'ILIKE', "%{$term}%")
                    ->orWhere('description', 'ILIKE', "%{$term}%");
            });
        }

        $q->orderByDesc('created_at');

        // если в фильтрах передана страница
        $page = !empty($filters['page']) ? (int) $filters['page'] : null;

        return $q->paginate(
            perPage: $perPage,
            page: $page
        );
    }

    public function existsSlug(string $slug, ?int $ignoreId = null): bool
    {
        $q = Recipe::where('slug', $slug);
        if ($ignoreId) {
            $q->where('id', '!=', $ignoreId);
        }
        return $q->exists();
    }

    /** Сгенерировать уникальный slug */
    protected function makeUniqueSlug(string $source, bool $isRawSlug = false, ?int $ignoreId = null): string
    {
        $base = Str::slug($source);
        $slug = $base ?: 'recipe';

        $i = 0;
        while ($this->existsSlug($slug, $ignoreId)) {
            $i++;
            $slug = "{$base}-{$i}";
        }
        return $slug;
    }

    public function calculateKBGUForRecipe(Recipe $recipe): void
    {
        $totalCalories = 0;
        $totalProteins = 0;
        $totalFats = 0;
        $totalCarbs = 0;

        foreach ($recipe->ingredients as $ingredient) {
            $weight = $ingredient->pivot->weight_grams;
            $ratio = $weight / 100;

            $totalCalories += ($ingredient->calories ?? 0) * $ratio;
            $totalProteins += ($ingredient->proteins ?? 0) * $ratio;
            $totalFats += ($ingredient->fats ?? 0) * $ratio;
            $totalCarbs += ($ingredient->carbs ?? 0) * $ratio;
        }

        $recipe->update([
            'calories_total' => (int) round($totalCalories),
            'proteins_total' => round($totalProteins, 2),
            'fats_total' => round($totalFats, 2),
            'carbs_total' => round($totalCarbs, 2),
        ]);
    }
}
