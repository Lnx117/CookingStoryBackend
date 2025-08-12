<?php
namespace App\Interfaces;

use Illuminate\Http\UploadedFile;

interface FileStoreServiceInterface {
    public function storeFromAttachment(array|int $fileIds, string $targetDir, string $disk): array;
    public function storeFromRequest(UploadedFile|array $files, string $targetDir, string $disk): array;
    public function getViewUrl(string $path, string $disk): string;
}
