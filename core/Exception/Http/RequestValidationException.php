<?php

namespace WaHelp\Core\Exception\Http;

use RuntimeException;
use Throwable;
use WaHelp\Core\Http\Request;

class RequestValidationException extends RuntimeException
{
    protected Request $request;

    public function __construct(
        Request $request,
        string $message = "",
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $this->request = $request;
        parent::__construct($message, $code, $previous);
    }

    public function getRequest(): Request
    {
        return $this->request;
    }
}