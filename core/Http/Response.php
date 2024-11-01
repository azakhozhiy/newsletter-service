<?php

namespace WaHelp\Core\Http;

class Response
{
    protected array $headers;
    protected int $httpCode;
    protected string $body;

    public function __construct(string $body = '', int $httpCode = 200, array $headers = [])
    {
        $this->httpCode = $httpCode;
        $this->body = $body;
        $this->headers = $headers;
    }

    public function setHeader(string $name, string $value): void
    {
        $this->headers[$name] = $value;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setHttpCode(int $httpCode): void
    {
        $this->httpCode = $httpCode;
    }

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function send(): void
    {
        http_response_code($this->httpCode);

        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        echo $this->body;
    }
}