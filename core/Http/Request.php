<?php

namespace WaHelp\Core\Http;

use Throwable;

class Request
{
    protected array $postData;
    protected array $getData;
    protected array $files;
    protected array $headers;
    protected string $method;
    protected array $input = [];

    public function __construct(array $postData, array $getData, array $files)
    {
        // Применяем фильтрацию с помощью array_map и htmlspecialchars
        $this->postData = $this->sanitizeArray($postData);
        $this->getData = $this->sanitizeArray($getData);
        $this->headers = $this->getHeadersFromGlobals();
        $this->files = $files;
        $this->method = $_SERVER['REQUEST_METHOD'];

        if ($this->isJson()) {
            $this->input = json_decode($this->getBody(), true, 512, JSON_THROW_ON_ERROR);
        }
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function header($key, $default = null): string
    {
        return $this->headers[$key] ?? $default;
    }

    public static function createFromGlobals(): static
    {
        return new static($_POST, $_GET, $_FILES);
    }

    public function get($key, $default = null): mixed
    {
        if (isset($this->getData[$key])) {
            return $this->sanitizeValue($this->getData[$key]);
        }

        return $this->sanitizeValue($default);
    }

    public function post($key, $default = null): mixed
    {
        if (isset($this->postData[$key])) {
            return $this->sanitizeValue($this->postData[$key]);
        }

        return $this->sanitizeValue($default);
    }

    public function file($key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function allGet(): array
    {
        return $this->getData;
    }

    public function all(): array
    {
        return array_merge($this->getData, $this->postData);
    }

    public function input(string $field): mixed
    {
        return $this->input[$field] ?? null;
    }

    public function allPost(): array
    {
        return $this->postData;
    }

    private function sanitizeArray(array $data): array
    {
        return array_map([$this, 'sanitizeValue'], $data);
    }

    private function sanitizeValue($value): mixed
    {
        return is_string($value) ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $value;
    }

    public function getBody(): string
    {
        return file_get_contents('php://input');
    }

    public function isJson(): bool
    {
        $contentType = $this->getHeadersFromGlobals()['content-type'] ?? null;

        return $contentType && $contentType === 'application/json';
    }

    private function getHeadersFromGlobals(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $header = str_replace('_', '-', strtolower(substr($key, 5)));
                $headers[$header] = $value;
            }
        }

        return $headers;
    }

    public function getUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        return html_entity_decode($uri, ENT_QUOTES, 'UTF-8');
    }
}