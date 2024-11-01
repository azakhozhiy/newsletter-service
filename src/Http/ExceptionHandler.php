<?php

namespace WaHelp\Newsletter\Http;

use Throwable;
use WaHelp\Core\Exception\Database\DatabaseException;
use WaHelp\Core\Exception\Http\RequestValidationException;
use WaHelp\Core\Exception\RouterException;
use WaHelp\Core\Http\JsonResponse;
use WaHelp\Core\Http\Response;
use WaHelp\Core\Routing\Router;

class ExceptionHandler
{
    public function handle(Throwable $e): Response
    {
        die($e);
        if ($e instanceof RequestValidationException) {
            return new JsonResponse([
                'error_code' => 'request-0001',
                'error_name' => 'REQUEST_VALIDATION_ERROR',
                'message' => $e->getMessage(),
            ], 422);
        }

        if ($e instanceof DatabaseException) {
            return new JsonResponse([
                'error_code' => 'system-0002',
                'error_name' => 'SYSTEM_SERVICE_ERROR',
                'message' => 'Service error. Please try again later.',
            ], 500);
        }

        if ($e instanceof RouterException) {
            return new JsonResponse([
                'error_code' => 'system-0003',
                'error_name' => 'ROUTE_ERROR',
                'message' => $e->getMessage(),
            ]);
        }

        return $this->baseErrorResponse($e);
    }

    public function baseErrorResponse(Throwable $e): Response
    {
        return new JsonResponse([
            'error_code' => 'system-0001',
            'error_name' => 'SYSTEM_UNKNOWN_ERROR',
            'message' => 'Unknown error. Please try again later.',
        ], 500);
    }
}