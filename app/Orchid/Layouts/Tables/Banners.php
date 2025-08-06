<?php

namespace App\Orchid\Layouts\Tables;

use App\Models\Banner;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\ModalToggle;

class Banners extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'banners';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('title', 'title')->sort()->cantHide()->filter(TD::FILTER_TEXT),
            TD::make('url', 'url')->sort()->cantHide()->filter(TD::FILTER_TEXT),
            TD::make('active', 'Активно')->render(function (Banner $banner) {
                return $banner->active ? 'Активно' : 'Не активно';
            })->sort()->cantHide(),
            TD::make('short_description', 'short_description')->sort()->cantHide()->filter(TD::FILTER_TEXT),
            TD::make('created_at', 'Создано')->sort()->defaultHidden(),
            TD::make('updated_at', 'Обновлено')->sort()->defaultHidden(),
            TD::make('Редактировать')
                ->render(function (Banner $banner) {
                    return ModalToggle::make('Редактировать')
                        ->modal('editBanner')
                        ->method('update')
                        ->modalTitle('Редактирование баннера:' . $banner->title)
                        ->asyncParameters([
                            'banner' => $banner->id,
                        ]);
                }),
            TD::make('Удалить')
                ->render(function (Banner $banner) {
                    return ModalToggle::make('Удалить')
                        ->modal('deleteBanner')
                        ->method('delete')
                        ->modalTitle('Удаление баннера:' . $banner->title)
                        ->asyncParameters([
                            'banner' => $banner->id,
                        ]);
                }),
        ];
    }
}
