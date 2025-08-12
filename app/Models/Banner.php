<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use EloquentFilter\Filterable as Filter;
use Orchid\Screen\AsSource;

class Banner extends Model
{
    use HasFactory;
    use AsSource;
    use Filterable;
    use Filter;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'banners';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'title',
        'url',
        'active',
        'short_description',
        'image_path',
    ];
}
