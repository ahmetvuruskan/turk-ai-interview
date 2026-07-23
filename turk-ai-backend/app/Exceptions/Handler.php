<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        DomainException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
     *
     * @return void
     */
    public function register(): void
    {

        $this->renderable(function (DomainException $e, $request) {
            if (!$this->shouldReturnJson($request, $e)) {
                return null;
            }

            return response()->json([
                'message' => $e->getMessage(),
                'status' => $e->getHttpStatus(),
                'data' => []
            ], $e->getHttpStatus());
        });

        $this->renderable(function (ValidationException $e, $request) {
            if (!$this->shouldReturnJson($request, $e)) {
                return null;
            }

            return response()->json([
                'message' => $e->getMessage(),
                'status' => Response::HTTP_BAD_REQUEST,
                'data' => [],
                'errors' => $e->errors()
            ], Response::HTTP_BAD_REQUEST);
        });

        $this->renderable(function (NotFoundHttpException|RouteNotFoundException $e, $request) {
            if (!$this->shouldReturnJson($request, $e)){
                return null;
            }

            $message = $e->getPrevious() instanceof ModelNotFoundException
                ? __('general.not_found')
                : $e->getMessage();

            return response()->json([
                'message' => $message,
                'status' => Response::HTTP_NOT_FOUND,
                'data' => [],
            ], Response::HTTP_NOT_FOUND);
        });


        $this->renderable(function (Throwable $e, $request) {
            if ($e instanceof HttpExceptionInterface || $e instanceof AuthenticationException) {
                return null;
            }

            if (!$this->shouldReturnJson($request, $e)) {
                return null;
            }

            if (config('app.debug') ) {
                return null;
            }

            return response()->json([
                'message' => __('general.server_error'),
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'data' => []
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        });
    }

    protected function unauthenticated($request, AuthenticationException $exception): JsonResponse|Response
    {
        if ($this->shouldReturnJson($request, $exception)) {
            return response()->json([
                'message' => __('general.unauthenticated'),
                'status' => Response::HTTP_UNAUTHORIZED,
                'data' => [],
            ], Response::HTTP_UNAUTHORIZED);
        }

        return parent::unauthenticated($request, $exception);
    }

    protected function shouldReturnJson($request, Throwable $e): bool
    {
        return $request->is('api/*') || $request->expectsJson();
    }
}
