<?php

namespace WaHelp\Core\Http;

use RuntimeException;

class JsonResponse extends Response
{
    public function __construct($data = [], int $httpCode = 200, array $headers = [])
    {
        // Устанавливаем заголовок Content-Type для JSON
        $headers['Content-Type'] = 'application/json';

        // Кодируем данные в JSON
        $body = json_encode($data);

        if ($body === false) {
            throw new RuntimeException('Failed to encode data to JSON: '.json_last_error_msg());
        }

        parent::__construct($body, $httpCode, $headers);
    }
}