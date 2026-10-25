<?php

/**
 * Example: Basic HTTP Kernel Usage
 *
 * This example demonstrates how to use the HTTP kernel with both
 * PSR-15 middleware (backward compatible) and Symfony events (new way).
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Nip\Http\Kernel\Event\RequestEvent;
use Nip\Http\Kernel\Event\ResponseEvent;
use Nip\Http\Kernel\Kernel;
use Nip\Http\Kernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// Example Event Subscriber (Symfony way - NEW)
class LoggingSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 10],
            KernelEvents::RESPONSE => ['onResponse', -10],
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        echo "🔵 [Event] Request received: " . $event->getRequest()->getPathInfo() . "\n";
    }

    public function onResponse(ResponseEvent $event): void
    {
        echo "🟢 [Event] Response ready: " . $event->getResponse()->getStatusCode() . "\n";
        
        // Add a custom header
        $event->getResponse()->headers->set('X-Processed-By', 'Symfony Events');
    }
}

// Example: Custom Kernel with event subscribers
class MyKernel extends Kernel
{
    protected function registerEventSubscribers(\Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher): void
    {
        // Register our logging subscriber
        $dispatcher->addSubscriber(new LoggingSubscriber());
    }
}

// Create application and router (simplified for example)
$app = new class {
    protected $container;
    
    public function share($name, $value) {
        return $this;
    }
    
    public function getContainer() {
        return $this->container ?? ($this->container = new class {
            public function get($name) {
                return null;
            }
            public function has($name) {
                return false;
            }
        });
    }
    
    public function terminate() {
        echo "🔴 [App] Application terminated\n";
    }
};

$router = new class {
    // Simplified router for example
};

// Create kernel
$kernel = new MyKernel($app, $router);

// Create a request
$request = Request::create('/api/users', 'GET');

echo "=== HTTP Kernel Example ===\n\n";
echo "Request: GET /api/users\n\n";

try {
    // Handle the request
    $response = $kernel->handle($request);
    
    echo "\nResponse Status: " . $response->getStatusCode() . "\n";
    echo "Response Headers:\n";
    foreach ($response->headers->all() as $name => $values) {
        foreach ($values as $value) {
            echo "  $name: $value\n";
        }
    }
    
    // Simulate sending the response
    echo "\n📤 Sending response...\n\n";
    
    // Terminate the kernel
    $kernel->terminate($request, $response);
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== End of Example ===\n";
