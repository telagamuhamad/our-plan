<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            if (app()->bound('sentry') && $this->shouldReport($e)) {
                \Sentry\captureException($e);
            }
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // Handle JSON/API requests with consistent format
        if ($request->expectsJson()) {
            return $this->handleApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Handle API exceptions with consistent JSON response.
     */
    protected function handleApiException(Request $request, Throwable $e)
    {
        $statusCode = 500;
        $message = 'Terjadi kesalahan pada server.';
        $errors = null;

        // Validation Exception
        if ($e instanceof ValidationException) {
            $statusCode = 422;
            $message = 'Validasi gagal.';
            $errors = $e->errors();
        }
        // Authentication Exception
        elseif ($e instanceof AuthenticationException) {
            $statusCode = 401;
            $message = 'Unauthorized. Silakan login kembali.';
        }
        // Model Not Found
        elseif ($e instanceof ModelNotFoundException) {
            $statusCode = 404;
            $message = 'Data tidak ditemukan.';
        }
        // Not Found HTTP Exception
        elseif ($e instanceof NotFoundHttpException) {
            $statusCode = 404;
            $message = 'Endpoint tidak ditemukan.';
        }
        // Access Denied
        elseif ($e instanceof AccessDeniedHttpException) {
            $statusCode = 403;
            $message = 'Anda tidak memiliki akses ke resource ini.';
        }
        // Too Many Requests
        elseif ($e instanceof TooManyRequestsHttpException) {
            $statusCode = 429;
            $message = 'Terlalu banyak permintaan. Silakan coba lagi dalam beberapa saat.';
        }
        // General exception with custom message
        else {
            // In production, don't expose detailed error messages
            if (config('app.debug')) {
                $message = $e->getMessage();
            }
        }

        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        // Add debug info in development
        if (config('app.debug') && !$e instanceof ValidationException) {
            $response['exception'] = get_class($e);
            $response['trace'] = collect($e->getTrace())->take(5)->toArray();
        }

        return response()->json($response, $statusCode);
    }
}
