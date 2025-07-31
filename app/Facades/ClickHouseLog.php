<?php
namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class ClickHouseLog extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\ClickHouseLogger::class;
    }
}
