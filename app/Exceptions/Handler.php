<?php

namespace Caronae\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public function render($request, Exception $e)
    {
        if ($request->expectsJson() && !($e instanceof ValidationException)) {
            if ($e instanceof ModelNotFoundException) {
                return response()->json(['error' => 'Not found'], 404);
            }

            $response = [
                'error' => 'Sorry, something went wrong.'
            ];

            if (config('app.debug')) {
                $response['exception'] = get_class($e);
                $response['message'] = $e->getMessage();
                $response['trace'] = $e->getTrace();
            }

            $status = $this->isHttpException($e) ? $e->getStatusCode() : 500;

            return response()->json($response, $status);
        }

        return parent::render($request, $e);
    }

}