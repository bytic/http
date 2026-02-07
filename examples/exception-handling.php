<?php

/**
 * Example: Event-Based Exception Handling
 *
 * This example shows how to handle exceptions using Symfony events,
 * which is more flexible than middleware-based error handling.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Nip\Http\Kernel\Event\ExceptionEvent;
use Nip\Http\Kernel\Kernel;
use Nip\Http\Kernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

// Custom exception types
class ValidationException extends \Exception {}
class AuthenticationException extends \Exception {}

// Exception handling subscriber
class ExceptionHandlerSubscriber implements EventSubscriberInterface
{
    protected bool $debug;

    public function __construct(bool $debug = false)
    {
        $this->debug = $debug;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // High priority to handle exceptions before other listeners
            KernelEvents::EXCEPTION => ['onKernelException', 100],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        echo "🔴 [Exception] " . get_class($exception) . ": " . $exception->getMessage() . "\n";

        // Determine response format
        $wantsJson = $request->headers->get('Accept') === 'application/json' 
            || str_starts_with($request->getPathInfo(), '/api/');

        // Handle different exception types
        $response = match (true) {
            $exception instanceof ValidationException => $this->handleValidationException($exception, $wantsJson),
            $exception instanceof AuthenticationException => $this->handleAuthException($exception, $wantsJson),
            $exception instanceof NotFoundHttpException => $this->handleNotFound($exception, $wantsJson),
            $exception instanceof HttpException => $this->handleHttpException($exception, $wantsJson),
            default => $this->handleGenericException($exception, $wantsJson),
        };

        // Set the response on the event
        $event->setResponse($response);
    }

    protected function handleValidationException(ValidationException $e, bool $wantsJson): Response
    {
        if ($wantsJson) {
            return new JsonResponse([
                'error' => 'Validation failed',
                'message' => $e->getMessage(),
            ], 422);
        }

        return new Response("Validation Error: " . $e->getMessage(), 422);
    }

    protected function handleAuthException(AuthenticationException $e, bool $wantsJson): Response
    {
        if ($wantsJson) {
            return new JsonResponse([
                'error' => 'Unauthorized',
                'message' => $e->getMessage(),
            ], 401);
        }

        return new Response("Unauthorized: " . $e->getMessage(), 401);
    }

    protected function handleNotFound(\Throwable $e, bool $wantsJson): Response
    {
        if ($wantsJson) {
            return new JsonResponse([
                'error' => 'Not Found',
                'message' => $e->getMessage(),
            ], 404);
        }

        return new Response("404 Not Found: " . $e->getMessage(), 404);
    }

    protected function handleHttpException(HttpException $e, bool $wantsJson): Response
    {
        if ($wantsJson) {
            return new JsonResponse([
                'error' => 'HTTP Error',
                'message' => $e->getMessage(),
                'code' => $e->getStatusCode(),
            ], $e->getStatusCode());
        }

        return new Response($e->getMessage(), $e->getStatusCode());
    }

    protected function handleGenericException(\Throwable $e, bool $wantsJson): Response
    {
        $statusCode = 500;
        $message = $this->debug ? $e->getMessage() : 'Internal Server Error';

        if ($wantsJson) {
            $data = [
                'error' => 'Internal Server Error',
                'message' => $message,
            ];

            if ($this->debug) {
                $data['trace'] = $e->getTraceAsString();
            }

            return new JsonResponse($data, $statusCode);
        }

        return new Response($message, $statusCode);
    }
}

// Custom Kernel with exception handling
class MyKernel extends Kernel
{
    protected function registerEventSubscribers(\Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher): void
    {
        // Register exception handler (debug mode enabled for example)
        $dispatcher->addSubscriber(new ExceptionHandlerSubscriber(true));
    }
}

// Example usage
echo "=== Exception Handling Example ===\n\n";

// Create kernel
$app = new class {
    public function share($name, $value) { return $this; }
    public function getContainer() { return new class { 
        public function get($name) { return null; }
        public function has($name) { return false; }
    }; }
    public function terminate() {}
};
$router = new class {};

$kernel = new MyKernel($app, $router);

// Simulate different exception scenarios
$scenarios = [
    ['path' => '/api/users', 'exception' => new ValidationException('Email is required')],
    ['path' => '/api/login', 'exception' => new AuthenticationException('Invalid credentials')],
    ['path' => '/page/not-found', 'exception' => new NotFoundHttpException('Page not found')],
    ['path' => '/api/error', 'exception' => new \RuntimeException('Database connection failed')],
];

foreach ($scenarios as $scenario) {
    echo "\n--- Scenario: {$scenario['path']} ---\n";
    
    $request = Request::create($scenario['path'], 'GET');
    $request->headers->set('Accept', 'application/json');
    
    try {
        // Simulate throwing an exception during request handling
        throw $scenario['exception'];
    } catch (\Throwable $e) {
        // Handle via event system
        $event = new ExceptionEvent($request, $e, \Symfony\Component\HttpKernel\HttpKernelInterface::MAIN_REQUEST);
        $kernel->getEventDispatcher()->dispatch($event, KernelEvents::EXCEPTION);
        
        if ($event->hasResponse()) {
            $response = $event->getResponse();
            echo "Response: {$response->getStatusCode()}\n";
            echo "Content: {$response->getContent()}\n";
        }
    }
}

echo "\n=== End of Example ===\n";
