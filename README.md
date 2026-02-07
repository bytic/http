# HTTP

ByTIC HTTP component - A Symfony-compliant HTTP kernel with PSR-7/PSR-15 middleware support

[![Latest Version on Packagist](https://img.shields.io/packagist/v/bytic/http.svg?style=flat-square)](https://packagist.org/packages/bytic/http)
[![Latest Stable Version](https://poser.pugx.org/bytic/http/v/stable)](https://packagist.org/packages/bytic/http)
[![Latest Unstable Version](https://poser.pugx.org/bytic/http/v/unstable)](https://packagist.org/packages/bytic/http)

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/travis/bytic/http/master.svg?style=flat-square)](https://travis-ci.org/bytic/framework)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/92329f47-7940-4b14-91e9-45330b887bdd/mini.png)](https://insight.sensiolabs.com/projects/92329f47-7940-4b14-91e9-45330b887bdd)
[![Quality Score](https://img.shields.io/scrutinizer/g/bytic/http.svg?style=flat-square)](https://scrutinizer-ci.com/g/bytic/http)
[![StyleCI](https://styleci.io/repos/118474281/shield?branch=master)](https://styleci.io/repos/118474281)
[![Total Downloads](https://img.shields.io/packagist/dt/bytic/http.svg?style=flat-square)](https://packagist.org/packages/bytic/http)

## Features

- **Symfony-compliant HTTP Kernel** - Implements Symfony's HttpKernelInterface with full event dispatching
- **PSR-7/PSR-15 Middleware Support** - Backward compatible with existing PSR middleware
- **Event-Driven Architecture** - Dispatch kernel events (REQUEST, RESPONSE, EXCEPTION, TERMINATE)
- **Request Stack Management** - Track main and sub-requests like Symfony
- **Controller Resolution** - Symfony-style controller and argument resolvers
- **Exception Handling** - Flexible exception handling with event subscribers
- **Fully Backward Compatible** - Works with existing middleware-based code

## Installation

```bash
composer require bytic/http
```

## Usage

### Basic Kernel Usage

```php
use Nip\Http\Kernel\Kernel;
use Nip\Application\Application;
use Nip\Router\Router;
use Symfony\Component\HttpFoundation\Request;

// Create kernel
$app = new Application();
$router = new Router();
$kernel = new Kernel($app, $router);

// Handle request
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();

// Terminate kernel
$kernel->terminate($request, $response);
```

### Event Subscribers (Symfony Way)

```php
use Nip\Http\Kernel\Event\ResponseEvent;
use Nip\Http\Kernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ResponseHeadersSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', 0],
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $response = $event->getResponse();
        $response->headers->set('X-Powered-By', 'ByTIC HTTP Kernel');
    }
}

// Register subscriber
$kernel->getEventDispatcher()->addSubscriber(new ResponseHeadersSubscriber());
```

### Available Kernel Events

- `KernelEvents::REQUEST` - Dispatched at the start of request handling
- `KernelEvents::CONTROLLER` - Dispatched before controller execution
- `KernelEvents::RESPONSE` - Dispatched before sending the response
- `KernelEvents::EXCEPTION` - Dispatched when an exception occurs
- `KernelEvents::TERMINATE` - Dispatched after response is sent

### Middleware (Backward Compatible)

The kernel still supports PSR-15 middleware:

```php
$kernel->pushMiddleware(MyMiddleware::class);
$kernel->prependMiddleware(AnotherMiddleware::class);
```

### Request Stack

Access the current request anywhere:

```php
$currentRequest = $kernel->getRequestStack()->getCurrentRequest();
$mainRequest = $kernel->getRequestStack()->getMainRequest();
```

## Migration from PSR-15 to Event Listeners

The new event system runs **alongside** the existing middleware, so you can migrate gradually:

### Before (Middleware)

```php
class MyMiddleware implements ServerMiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Modify request
        $request = $request->withAttribute('foo', 'bar');
        
        // Get response
        $response = $handler->handle($request);
        
        // Modify response
        $response = $response->withHeader('X-Custom', 'value');
        
        return $response;
    }
}
```

### After (Event Subscriber)

```php
class MyEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 10],
            KernelEvents::RESPONSE => ['onResponse', 10],
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $request->attributes->set('foo', 'bar');
    }

    public function onResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $response->headers->set('X-Custom', 'value');
    }
}
```

## Architecture

This package follows Symfony's kernel architecture:

1. **Request Event** - Early request processing, can short-circuit with a response
2. **Controller Resolution** - Determine which controller to execute
3. **Middleware Stack** - Execute PSR-15 middleware (backward compatible)
4. **Response Event** - Modify the response before sending
5. **Terminate Event** - Clean up after response is sent

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
