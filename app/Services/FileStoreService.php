<?php

namespace App\Services;

use App\Interfaces\FileStoreServiceInterface;
use Illuminate\Support\Facades\Storage;
use Orchid\Attachment\Models\Attachment;
use Illuminate\Http\UploadedFile;


class FileStoreService implements FileStoreServiceInterface
{

    public function storeFromAttachment($fileIds, string $targetDir = 'files', string $disk = 'minio_files'): array
    {
        $fileIds = is_array($fileIds) ? $fileIds : [$fileIds];
        $paths = [];

        foreach ($fileIds as $id) {
            $attachment = Attachment::find($id);

            if (!$attachment) {
                throw new \RuntimeException("Attachment {$id} not found");
            }

            $paths[] = $this->saveFileToMinio(
                Storage::disk($attachment->disk)->get($attachment->path.$attachment->name.'.'.$attachment->extension),
                $attachment->mime,
                $attachment->extension,
                $disk,
                $targetDir
            );
        }

        return $paths;
    }

    public function storeFromRequest(UploadedFile|array $files, string $targetDir, string $disk = 'minio_files'): array
    {
        $files = is_array($files) ? $files : [$files];
        $paths = [];

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                throw new \InvalidArgumentException('Expected UploadedFile instance');
            }

            $paths[] = $this->saveFileToMinio(
                file_get_contents($file->getRealPath()),
                $file->getMimeType(),
                $file->getClientOriginalExtension(),
                $disk,
                $targetDir
            );
        }

        return $paths;
    }

    private function saveFileToMinio(string $fileContent, string $mimeType, string $extension, string $disk, string $targetDir): string
    {
        $fileName = "{$targetDir}/" . uniqid('', true) . '.' . $extension;
        Storage::disk($disk)->put($fileName, $fileContent);

        return $fileName;
    }

    public function getViewUrl(string $path, string $disk = 'minio_files'): string
    {
        return Storage::disk($disk)->url($path);
    }

    public function getDownloadUrl(string $path, string $disk = 'minio_files'): string
    {
        return route('files.download', ['path' => $path]);
    }
}
