<?php

namespace App\Orchid\Attachment;

use Orchid\Attachment\Engines\Generator;

class BannerAttachment extends Generator
{
    protected ?string $disk = 'minio_files'; // диск для хранения
    protected ?string $path = 'banners';     // папка в диске

    public function path(string $name = ''): string
    {
        return trim($this->path ?? '', '/') . '/' . $name;
    }
}
