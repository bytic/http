<?php

namespace Nip\Http\Exceptions;

use Exception;
use Nip\Container\Container;
use Nip\Debug\Exceptions\Renderable\RenderableExceptionInterface;
use Nip\Http\Response\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run as Whoops;

/**
 * Class Handler
 * @package Nip\Http\Exceptions
 */
class Handler
{
    /**
     * The container implementation.
     *
     * @var Container
     */
    protected $container;

    /**
     * Create a new exception handler instance.
     *
     * @param Container $container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param $request
     * @param Exception $exception
     * @return ResponseInterface|JsonResponse|Response
     */
    public function render($request, Throwable $exception)
    {
        $exception = $this->prepareException($exception);

        return $request->expectsJson()
            ? $this->prepareJsonResponse($request, $exception)
            : $this->prepareResponse($request, $exception);
    }

    /**
     * Prepare exception for rendering.
     * @param Throwable $e
     * @return Throwable
     */
    protected function prepareException(Throwable $e)
    {
//        if ($e instanceof ModelNotFoundException) {
//            $e = new NotFoundHttpException($e->getMessage(), $e);
//        } elseif ($e instanceof AuthorizationException) {
//            $e = new AccessDeniedHttpException($e->getMessage(), $e);
//        } elseif ($e instanceof TokenMismatchException) {
//            $e = new HttpException(419, $e->getMessage(), $e);
//        } elseif ($e instanceof SuspiciousOperationException) {
//            $e = new NotFoundHttpException('Bad hostname provided.', $e);
//        }

        return $e;
    }

    /**
     * Prepare a JSON response for the given exception.
     *
     * @param Request $request
     * @param Throwable $e
     * @return JsonResponse
     */
    protected function prepareJsonResponse($request, Throwable $e)
    {
        return new JsonResponse(
            $this->convertExceptionToArray($e),
            $this->isHttpException($e) ? $e->getStatusCode() : 500,
            $this->isHttpException($e) ? $e->getHeaders() : [],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Convert the given exception to an array.
     *
     * @param Throwable $e
     * @return array
     */
    protected function convertExceptionToArray(Throwable $e)
    {
        return $this->isDebug() ? [
            'message' => $e->getMessage(),
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
//            'trace' => collect($e->getTrace())->map(function ($trace) {
//                return Arr::except($trace, ['args']);
//            })->all(),
        ] : [
            'message' => $this->isHttpException($e) ? $e->getMessage() : 'Server Error',
        ];
    }

    /**
     * Prepare a response for the given exception.
     *
     * @param Request $request
     * @param Throwable $exception
     * @return Response
     */
    protected function prepareResponse($request, Throwable $exception)
    {
        if (!$this->isHttpException($exception) && $this->isDebug()) {
            return $this->convertExceptionToResponse($exception);
        }

        if (!$this->isHttpException($exception)) {
            $exception = new HttpException(500, $exception->getMessage());
        }

        return $this->renderHttpException($exception);
    }

    /**
     * Create a Symfony response for the given exception.
     *
     * @param Throwable $e
     * @return Response
     */
    protected function convertExceptionToResponse(Throwable $e)
    {
        return ResponseFactory::make(
            $this->renderExceptionContent($e),
            $this->isHttpException($e) ? $e->getStatusCode() : 500,
            $this->isHttpException($e) ? $e->getHeaders() : []
        );
    }

    /**
     * Get the response content for the given exception.
     *
     * @param Throwable $exception
     * @return string
     */
    protected function renderExceptionContent(Throwable $exception)
    {
        try {
            if ($this->isDebug() && class_exists(Whoops::class)) {
                return $this->renderExceptionWithWhoops($exception);
            }
            if ($exception instanceof RenderableExceptionInterface) {
                return $exception->getMessagePublic();
            }
            return $this->renderExceptionWithSymfony($exception, $this->isDebug());
        } catch (Exception $exception) {
            return $this->renderExceptionWithSymfony($exception, $this->isDebug());
        }
    }

    /**
     * @param Throwable $e
     * @return ResponseInterface
     */
    protected function renderExceptionWithWhoops(Throwable $e)
    {
        $whoops = new Whoops;
        $whoops->allowQuit(false);
        $whoops->writeToOutput(false);
        $whoops->pushHandler(new PrettyPageHandler());

        return ResponseFactory::make($whoops->handleException($e));
    }

    /**
     * Render an exception to a string using Symfony.
     *
     * @param Throwable $e
     * @param bool $debug
     * @return string
     */
    protected function renderExceptionWithSymfony(Throwable $e, $debug)
    {
        $renderer = new HtmlErrorRenderer($debug);

        return $renderer->render($e)->getAsString();
    }

    /**
     * Render the given HttpException.
     *
     * @param \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e
     * @return Response
     */
    protected function renderHttpException(HttpExceptionInterface $e)
    {
//        $this->registerErrorViewPaths();
//
//        if (view()->exists($view = $this->getHttpExceptionView($e))) {
//            return response()->view($view, [
//                'errors' => new ViewErrorBag,
//                'exception' => $e,
//            ], $e->getStatusCode(), $e->getHeaders());
//        }

        return $this->convertExceptionToResponse($e);
    }

    /**
     * Determine if the given exception is an HTTP exception.
     *
     * @param Throwable $e
     * @return bool
     */
    protected function isHttpException(Throwable $e)
    {
        return $e instanceof HttpExceptionInterface;
    }

    /**
     * @return bool
     */
    public function isDebug()
    {
        $config = config('app.debug');
        return $config === true || $config === 'true' || $config === '1' || $config === 1;
    }
}
