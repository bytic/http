# Migration Guide: PSR-15 Middleware to Symfony Events

This guide helps you migrate from PSR-15 middleware to Symfony-style event listeners.

## Why Migrate?

- **Better separation of concerns** - Events are more granular than middleware
- **More flexibility** - Multiple listeners can handle the same event with different priorities
- **Symfony ecosystem compatibility** - Use Symfony bundles and components seamlessly
- **Better testability** - Events are easier to test in isolation

## Migration is Optional

**Important:** You don't have to migrate immediately! The kernel supports both systems running simultaneously. You can:

1. Keep using existing PSR-15 middleware
2. Add new functionality as event subscribers
3. Gradually migrate middleware to events over time

## Step-by-Step Migration

### 1. Understanding the Mapping

| Middleware Phase | Event Equivalent | Priority |
|-----------------|------------------|----------|
| Before handling | `KernelEvents::REQUEST` | 10-100 |
| Controller resolution | `KernelEvents::CONTROLLER` | 0 |
| After handling | `KernelEvents::RESPONSE` | -100 to -10 |
| After response sent | `KernelEvents::TERMINATE` | Any |
| Exception handling | `KernelEvents::EXCEPTION` | 0 |

### 2. Convert Middleware to Event Subscriber

#### Before: PSR-15 Middleware

```php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthenticationMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request, 
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Check authentication
        if (!$this->isAuthenticated($request)) {
            return new Response('Unauthorized', 401);
        }
        
        // Add user to request
        $request = $request->withAttribute('user', $this->getUser());
        
        return $handler->handle($request);
    }
}
```

#### After: Event Subscriber

```php
use Nip\Http\Kernel\Event\RequestEvent;
use Nip\Http\Kernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            // High priority to run early
            KernelEvents::REQUEST => ['onKernelRequest', 100],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        
        // Check authentication
        if (!$this->isAuthenticated($request)) {
            // Short-circuit the request
            $event->setResponse(new Response('Unauthorized', 401));
            return;
        }
        
        // Add user to request attributes
        $request->attributes->set('user', $this->getUser());
    }
}
```

### 3. Register Event Subscribers

#### Option 1: Directly with EventDispatcher

```php
$kernel->getEventDispatcher()->addSubscriber(new AuthenticationSubscriber());
```

#### Option 2: Override registerEventSubscribers() in Kernel

```php
class MyKernel extends Kernel
{
    protected function registerEventSubscribers(EventDispatcherInterface $dispatcher): void
    {
        $dispatcher->addSubscriber(new AuthenticationSubscriber());
        $dispatcher->addSubscriber(new LoggingSubscriber());
        $dispatcher->addSubscriber(new CorsSubscriber());
    }
}
```

### 4. Common Migration Patterns

#### Pattern 1: Request Modification

```php
// Middleware: $request = $request->withAttribute('key', 'value');
// Event: $request->attributes->set('key', 'value');

public function onKernelRequest(RequestEvent $event): void
{
    $event->getRequest()->attributes->set('key', 'value');
}
```

#### Pattern 2: Response Modification

```php
// Middleware: $response = $response->withHeader('X-Custom', 'value');
// Event: $response->headers->set('X-Custom', 'value');

public function onKernelResponse(ResponseEvent $event): void
{
    $event->getResponse()->headers->set('X-Custom', 'value');
}
```

#### Pattern 3: Short-Circuit Response

```php
// Middleware: return new Response('content');
// Event: $event->setResponse(new Response('content'));

public function onKernelRequest(RequestEvent $event): void
{
    if ($this->shouldShortCircuit()) {
        $event->setResponse(new Response('content'));
    }
}
```

#### Pattern 4: Exception Handling

```php
// Middleware: try/catch in process()
// Event: subscribe to EXCEPTION event

public static function getSubscribedEvents(): array
{
    return [
        KernelEvents::EXCEPTION => ['onKernelException', 10],
    ];
}

public function onKernelException(ExceptionEvent $event): void
{
    $exception = $event->getThrowable();
    
    if ($exception instanceof MyException) {
        $event->setResponse(new JsonResponse([
            'error' => $exception->getMessage()
        ], 400));
    }
}
```

### 5. Priority Management

Event listeners execute in priority order (higher = earlier):

```php
public static function getSubscribedEvents(): array
{
    return [
        // Run first (authentication, security)
        KernelEvents::REQUEST => ['onRequest', 100],
        
        // Run in the middle (business logic)
        KernelEvents::REQUEST => ['onRequest', 0],
        
        // Run last (logging, cleanup)
        KernelEvents::REQUEST => ['onRequest', -100],
    ];
}
```

### 6. Testing Event Subscribers

```php
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class AuthenticationSubscriberTest extends TestCase
{
    public function testAuthentication()
    {
        $subscriber = new AuthenticationSubscriber();
        $request = Request::create('/api/user');
        $event = new RequestEvent($request, HttpKernelInterface::MAIN_REQUEST);
        
        $subscriber->onKernelRequest($event);
        
        $this->assertTrue($event->hasResponse());
        $this->assertEquals(401, $event->getResponse()->getStatusCode());
    }
}
```

## Gradual Migration Strategy

1. **Phase 1:** Keep all existing middleware, add new features as events
2. **Phase 2:** Migrate low-risk middleware (logging, headers)
3. **Phase 3:** Migrate business logic middleware
4. **Phase 4:** Migrate critical middleware (authentication, security)
5. **Phase 5:** Remove middleware support (optional, far future)

## Best Practices

1. **Use high priorities (100+) for security/authentication**
2. **Use low priorities (-100) for logging/cleanup**
3. **Keep event subscribers focused on a single concern**
4. **Test subscribers independently of the kernel**
5. **Document event subscriber priorities in comments**

## Need Help?

- Review the example subscribers in `src/Kernel/EventSubscriber/`
- Check Symfony's event dispatcher documentation
- Look at the test cases in `tests/src/Kernel/Event/`

## Backward Compatibility

All PSR-15 middleware will continue to work. The kernel:

1. Dispatches REQUEST event
2. Executes PSR-15 middleware stack
3. Dispatches RESPONSE event
4. Dispatches TERMINATE event after response sent

This ensures your existing code keeps working while you migrate to events.
