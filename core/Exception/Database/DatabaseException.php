<?php

namespace WaHelp\Core\Exception\Database;

use RuntimeException;
use Throwable;

class DatabaseException extends RuntimeException
{
    public function __construct(string $message = "", ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}