<?php

namespace App\Services;

use App\Enums\LogLevels;
use ClickHouseDB\Client;
use Illuminate\Support\Facades\Log;

class ClickHouseLogger
{
    protected array $config;
    protected ?Client $client = null;

    public function __construct()
    {
        try {
            $this->config = [
                'host' => env('CLICKHOUSE_HOST', 'localhost'),
                'port' => env('CLICKHOUSE_PORT', 8123),
                'username' => env('CLICKHOUSE_USER', 'default'),
                'password' => env('CLICKHOUSE_PASSWORD', ''),
                'database' => env('CLICKHOUSE_DATABASE', 'default'),
            ];

            $this->client = new Client($this->config);
            $this->client->database($this->config['database']);
        } catch (Throwable $e) {
            // Если ClickHouse недоступен — просто пишем в обычный лог
            Log::warning('ClickHouse подключение не удалось: ' . $e->getMessage());
            $this->client = null; // не создаём клиента
        }
    }

    public function log(LogLevels $logLevel, string $message, array $context = [], array $extra = []): void
    {
        // Laravel лог
        Log::{$logLevel->value}($message, $context);

        // ClickHouse лог
        $this->logToClickHouse($logLevel, $message, $context, $extra);
    }

    protected function logToClickHouse(LogLevels $logLevel, string $message, array $context = [], array $extra = []): void
    {
        if (!$this->client) {
            return;
        }

        try {
            // Данные для вставки
            $data = [
                [
                    'timestamp' => date('Y-m-d H:i:s'),
                    'level' => $logLevel->value,
                    'message' => $message,
                    'context' => json_encode($context),
                    'extra' => json_encode($extra),
                ]
            ];

            // Вставляем данные в таблицу logs
            $this->client->insert('logs', $data);
        } catch(Throwable $e) {
            // Ошибка записи — не мешаем работе приложения
            Log::warning('Ошибка логирования в ClickHouse: ' . $e->getMessage());
        }

    }
}
