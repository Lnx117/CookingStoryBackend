<?php

namespace App\Orchid\Screens\Tables;

use App\Enums\LogLevels;
use App\Facades\ClickHouseLog;
use App\Http\Requests\BannerUpdateRequest;
use App\Interfaces\BannerRepositoryInterface;
use App\Interfaces\BannerServiceInterface;
use App\Models\Banner;
use App\Orchid\Layouts\Tables\Banners;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Upload;

class BannerScreen extends Screen
{
    protected BannerServiceInterface $service;

    public function __construct(BannerServiceInterface $service) {
        $this->service = $service;
    }

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'banners' => Banner::filters()->paginate(15),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Баннеры';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Создать')->modal('createBanner')->method('create'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Banners::class,
            Layout::modal('editBanner', [
                Layout::rows([
                    Input::make('banner.id')->type('hidden'),
                    Input::make('banner.title')
                        ->title('Заголовок')
                        ->required(),
                    Input::make('banner.code')
                        ->title('Код')
                        ->value('')
                        ->required(),
                    Upload::make('banner.image')
                        ->storage('minio_files') // диск из filesystems.php
                        ->path('banners')
                        ->maxFiles(1)
                        ->acceptedFiles('image/*'),
                    CheckBox::make('banner.active')
                        ->title('Активность'),
                    TextArea::make('banner.short_description')
                        ->maxLength(500)
                        ->title('Краткое описание')
                        ->required(),
                ]),
            ])->size(Modal::SIZE_LG)->applyButton('Обновить')->async('asyncGetBanner'),

            Layout::modal('createBanner', [
                Layout::rows([
                    Input::make('banner.title')
                        ->title('Заголовок')
                        ->value('')
                        ->required(),
                    Input::make('banner.code')
                        ->title('Код')
                        ->value('')
                        ->required(),
                    Upload::make('banner.image')
                        ->storage('minio_files') // диск из filesystems.php
                        ->maxFiles(1)
                        ->acceptedFiles('image/*'),
                    CheckBox::make('banner.active')
                        ->value(true)
                        ->title('Активность'),
                    TextArea::make('banner.short_description')
                        ->maxLength(500)
                        ->title('Краткое описание')
                        ->value('')
                        ->required(),
                ])
            ])->size(Modal::SIZE_LG)->title('Создание баннера')->applyButton('Создать'),


            Layout::modal('deleteBanner', [
                Layout::rows([
                    Input::make('banner.id')->type('hidden'),
                    Input::make('banner.title')
                        ->disabled()
                        ->title('Заголовок')
                        ->required(),
                    Input::make('banner.url')
                        ->disabled()
                        ->title('Ссылка')
                        ->required(),
                    CheckBox::make('banner.active')
                        ->title('Активность'),
                    TextArea::make('banner.short_description')
                        ->disabled()
                        ->maxLength(500)
                        ->title('Краткое описание')
                        ->required(),
                ]),
            ])->size(Modal::SIZE_LG)->applyButton('Удалить')->async('asyncGetBanner'),


        ];
    }

    public function create(BannerUpdateRequest $request): void
    {
        try {
            $this->service->orchidCreateBanner($request);
            Toast::info('Баннер успешно добавлен');
        } catch (\Throwable $e) {
            ClickHouseLog::log(LogLevels::ERROR, 'Ошибка при создании баннера из админки', ['Error' => $e->getMessage()]);
            Toast::error('Произошла ошибка при добавлении баннера: ' . $e->getMessage());
        }
    }

    public function update(BannerUpdateRequest $request): void
    {
        try {
            $this->service->orchidUpdateBanner($request);
            Toast::info('Баннер успешно обновлен');

        } catch (\Throwable $e) {
            ClickHouseLog::log(LogLevels::ERROR, 'Ошибка при обновлении баннера из админки', ['Error' => $e->getMessage()]);
            Toast::error('Произошла ошибка при обновлении баннера: ' . $e->getMessage());
        }

    }

    public function delete(Request $request)
    {
        try {
            $this->service->orchidDeleteBanner($request);
            Toast::info('Баннер успешно удален');
        } catch (\Throwable $e) {
            ClickHouseLog::log(LogLevels::ERROR, 'Ошибка при удалении баннера из админки', ['Error' => $e->getMessage()]);
            Toast::error('Ошибка при удалении баннера из админки');
        }
    }

    public function asyncGetBanner(Banner $banner): array
    {
        return [
            'banner' => $banner,
        ];
    }
}

