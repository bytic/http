<?php

/**
 * Example: Middleware Groups
 *
 * This example demonstrates how to organize middleware into groups
 * for different parts of your application (web, api, etc.)
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Nip\Http\Kernel\Kernel;

// Example: Extended Kernel with middleware groups
class ApplicationKernel extends Kernel
{
    /**
     * The application's global middleware stack.
     *
     * These middleware are run during every request to your application.
     */
    protected array $middleware = [
        // \App\Http\Middleware\TrustProxies::class,
        // \App\Http\Middleware\CheckForMaintenanceMode::class,
    ];

    /**
     * The application's route middleware groups.
     */
    protected $middlewareGroups = [
        'web' => [
            // \App\Http\Middleware\EncryptCookies::class,
            // \App\Http\Middleware\AddQueuedCookiesToResponse::class,
            // \App\Http\Middleware\StartSession::class,
            // \App\Http\Middleware\VerifyCsrfToken::class,
        ],

        'api' => [
            // \App\Http\Middleware\ThrottleRequests::class,
            // \App\Http\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ];
}

// Usage example
echo "=== Middleware Groups Example ===\n\n";

// Create kernel (simplified for example)
$app = new class {
    public function share($name, $value) { return $this; }
    public function getContainer() { return new class { 
        public function get($name) { return null; }
        public function has($name) { return false; }
    }; }
    public function terminate() {}
};
$router = new class {};

$kernel = new ApplicationKernel($app, $router);

// Register middleware groups
$kernel->middlewareGroup('web', [
    'StartSession',
    'VerifyCsrfToken',
]);

$kernel->middlewareGroup('api', [
    'ThrottleRequests:60,1',
    'SubstituteBindings',
]);

// Register individual middleware
$kernel->routeMiddleware('auth', 'AuthMiddleware');
$kernel->routeMiddleware('guest', 'GuestMiddleware');

echo "Web Middleware Group:\n";
print_r($kernel->getMiddlewareGroup('web'));

echo "\nAPI Middleware Group:\n";
print_r($kernel->getMiddlewareGroup('api'));

echo "\nRoute Middleware:\n";
print_r($kernel->getRouteMiddleware());

echo "\n=== End of Example ===\n";
