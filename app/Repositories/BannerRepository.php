<?php
namespace App\Repositories;

use App\Http\Requests\BannerUpdateRequest;
use App\Models\Banner;
use App\Interfaces\BannerRepositoryInterface;
use App\Interfaces\FileStoreServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Orchid\Attachment\Models\Attachment;

class BannerRepository implements BannerRepositoryInterface
{
    protected Banner $model;
    protected FileStoreServiceInterface $fileStoreService;

    public function __construct(Banner $banner, FileStoreServiceInterface $fileStoreService) {
        $this->model = $banner;
        $this->fileStoreService = $fileStoreService;
    }

    public function orchidCreateBanner(BannerUpdateRequest $request): Banner
    {
        $validated = $request->validated();

        $data = [
            'title' => $validated['title'],
            'code' => $validated['code'] ?? null,
            'short_description' => $validated['short_description'],
            'active' => $validated['active'] ?? false,
        ];
        if (!empty($validated['image'])) {
            // Передаем массив ID-шников (upload в orchid возвращает id)
            $id = is_array($validated['image']) ? $validated['image'][0] : $validated['image'];
            $paths = $this->fileStoreService->storeFromAttachment($id, 'banners');
            $data['image_path'] = $paths[0];
        }

        return $this->model->create($data);
    }

    public function orchidUpdateBanner(BannerUpdateRequest $request): bool
    {
        $banner = $this->model->findOrFail($request->input('banner.id'));
        return $banner->update($request->validated());
    }

    public function orchidDeleteBanner(Request $request): bool
    {
        $banner = $this->model->findOrFail($request->input('banner.id'));
        return $banner->delete();
    }

    protected function processAndStoreImage($imageIds): string
    {
        $manager = new ImageManager('gd');

        $id = is_array($imageIds) ? $imageIds[0] : $imageIds;

        $attachment = Attachment::find($id);

        if (!$attachment) {
            throw new \RuntimeException('Image not found');
        }

        $path = $attachment->path . $attachment->name . '.' . $attachment->extension;

        // Получаем содержимое файла из диска, указанного у attachment (minio_files)
        $fileContent = Storage::disk($attachment->disk)->get($path);

        // Обработка через Intervention Image
        $image = $manager->make($fileContent)
            ->resize(1200, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->encode('webp', 85);

        $fileName = 'banners/' . uniqid() . '.webp';

        // Сохраняем в нужный диск minio_banners (укажи в config/filesystems.php)
        Storage::disk('minio_banners')->put($fileName, (string) $image);

        return $fileName;
    }

    public function getBanners(array $filter): array
    {
        $banners = Banner::filter($filter)->get();

        $grouped = $banners->groupBy('code');

        return $grouped->toArray();
    }

}
