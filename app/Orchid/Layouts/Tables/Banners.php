<?php

namespace App\Orchid\Layouts\Tables;

use App\Enums\LogLevels;
use App\Facades\ClickHouseLog;
use App\Interfaces\FileStoreServiceInterface;
use App\Models\Banner;
use App\Services\FileStoreService;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\ModalToggle;

class Banners extends Table
{
    protected FileStoreServiceInterface $fileStoreService;
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'banners';

    public function __construct(FileStoreServiceInterface $fileStoreService){
        $this->fileStoreService = $fileStoreService;
    }

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('title', 'title')->sort()->cantHide()->filter(TD::FILTER_TEXT),
            TD::make('code', 'code')->sort()->cantHide()->filter(TD::FILTER_TEXT),
            TD::make('image_path', 'image_path')->sort()->cantHide()->filter(TD::FILTER_TEXT),
            TD::make('active', 'Активно')->render(function (Banner $banner) {
                return $banner->active ? 'Активно' : 'Не активно';
            })->sort()->cantHide(),
            TD::make('image_path', 'Изображение')
                ->style('width:100px; max-width:100px;')
                ->render(function (Banner $banner) {
                if (!$banner->image_path) {
                    return '';
                }

                $url = $this->fileStoreService->getViewUrl($banner->image_path);

                return "<img src='{$url}' alt='preview' style='height:50px; object-fit:contain;' />";
            }),
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
