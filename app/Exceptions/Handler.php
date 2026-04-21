<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof TokenMismatchException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sesija baigėsi. Prisijunkite iš naujo.'
                ], 419);
            }

            return redirect()
                ->route('login')
                ->with('error', 'Jūsų sesija baigėsi. Prisijunkite iš naujo.');
        }
        
        if ($request->expectsJson()) {

            if ($exception instanceof ModelNotFoundException || $exception instanceof NotFoundHttpException) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Resource not found',
                    'code' => 404
                ], 404);
            }

            if ($exception instanceof AuthenticationException) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized',
                    'code' => 401
                ], 401);
            }

            if ($exception instanceof ValidationException) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $exception->errors(),
                    'code' => 422
                ], 422);
            }

            if (method_exists($exception, 'getStatusCode') && $exception->getStatusCode() === 403) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Forbidden',
                    'code' => 403
                ], 403);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Server error',
                'code' => 500
            ], 500);
        }

        return parent::render($request, $exception);
    }
}
