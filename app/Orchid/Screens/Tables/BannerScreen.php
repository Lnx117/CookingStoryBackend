<?php

namespace App\Orchid\Screens\Tables;

use App\Http\Requests\BannerUpdateRequest;
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

class BannerScreen extends Screen
{
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
                    Input::make('banner.url')
                        ->title('Ссылка')
                        ->required(),
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
                    Input::make('banner.url')
                        ->title('Ссылка')
                        ->value('')
                        ->required(),
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
            Banner::create($request->validated());

            Toast::info('Баннер успешно добавлен');

        } catch (\Exception $e) {
            Toast::error('Произошла ошибка при добавлении баннера: ' . $e->getMessage());
        }
    }

    public function update(BannerUpdateRequest $request): void
    {
        try {
            $banner = Banner::find($request->input('banner.id'))->update($request->validated());
            Toast::info('Баннер успешно обновлен');

        } catch (\Exception $e) {
            Toast::error('Произошла ошибка при обновлении баннера: ' . $e->getMessage());
        }

    }

    public function delete(Request $request)
    {
        try {
            $banner = Banner::findOrFail($request->input('banner.id'));
            $banner->delete();

            Toast::info('Баннер успешно удален');
        } catch (\Exception $e) {
            Toast::error($e->getMessage());
        }
    }

    public function asyncGetBanner(Banner $banner): array
    {
        return [
            'banner' => $banner,
        ];
    }
}

