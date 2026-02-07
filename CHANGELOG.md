# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

#### Event System (Symfony-compliant)
- Added Symfony EventDispatcher integration to HTTP Kernel
- Implemented kernel event classes:
  - `RequestEvent` - Early request processing, can short-circuit with response
  - `ResponseEvent` - Modify response before sending
  - `ExceptionEvent` - Handle exceptions and create error responses
  - `TerminateEvent` - Execute cleanup after response is sent
  - `ControllerEvent` - Filter/modify controller callable
  - `ViewEvent` - Handle non-Response controller returns
  - `FinishRequestEvent` - Clean up after request processing
- Added `KernelEvents` constants class with event name definitions
- Added `HasEventDispatcher` trait for event dispatching capabilities
- Event dispatching now runs alongside existing middleware (backward compatible)

#### Request Handling
- Added `RequestStack` for tracking main and sub-requests
- Added `HasRequestStack` trait for request stack management
- Implemented proper request lifecycle with push/pop semantics
- Request stack integrated into kernel handle() method

#### Controller Resolution
- Added `ControllerResolver` following Symfony conventions
- Added `ArgumentResolver` for resolving controller dependencies
- Added `HasControllerResolver` trait for controller resolution capabilities
- Support for Symfony-style `_controller` request attribute

#### Middleware Management
- Implemented middleware group registration (`middlewareGroup()`)
- Implemented route middleware registration (`routeMiddleware()`)
- Added methods to retrieve middleware groups and route middleware
- Middleware groups are now properly organized (web, api, etc.)

#### Example Event Subscribers
- `ResponseHeadersSubscriber` - Example for adding custom response headers
- `ExceptionSubscriber` - Example for handling exceptions via events

#### Documentation & Examples
- Updated README with comprehensive feature list and usage examples
- Added MIGRATION.md guide for migrating from PSR-15 to Symfony events
- Created example files:
  - `examples/basic-kernel.php` - Basic kernel usage with events
  - `examples/middleware-groups.php` - Organizing middleware
  - `examples/exception-handling.php` - Event-based exception handling

#### Tests
- Added `RequestEventTest` - Tests for request event functionality
- Added `ResponseEventTest` - Tests for response event functionality
- Added `ExceptionEventTest` - Tests for exception event functionality

### Changed

#### Kernel Lifecycle
- Enhanced `Kernel::handle()` to dispatch REQUEST, RESPONSE events
- Enhanced `Kernel::terminate()` to dispatch TERMINATE event
- Updated exception handling to dispatch EXCEPTION event
- Added `filterResponse()` method for response event dispatching
- Added `handleThrowable()` method for exception event dispatching
- Request stack integration with automatic push/pop in handle()

#### Code Quality
- Fixed deprecated `MASTER_REQUEST` constant usage (now uses `MAIN_REQUEST`)
- Improved PHPDoc comments across all classes
- Clarified FinishRequestEvent documentation
- Removed external framework dependencies from examples

### Deprecated

- `KernelEvent::isMasterRequest()` - Use `isMainRequest()` instead
- `RequestStack::getMasterRequest()` - Use `getMainRequest()` instead

### Backward Compatibility

All existing PSR-15 middleware continues to work without changes. The kernel executes:
1. REQUEST event
2. Middleware stack (existing behavior)
3. RESPONSE event
4. TERMINATE event

This allows gradual migration from middleware to event subscribers.

## Architecture

The new architecture follows Symfony's kernel flow:

```
Request → REQUEST Event
       → Middleware Stack (backward compatible)
       → RESPONSE Event
       → Response
       → TERMINATE Event
```

Exception flow:

```
Exception → EXCEPTION Event
         → Response
         → RESPONSE Event
         → Response
```

## Migration Path

Users can migrate gradually:
1. Keep existing PSR-15 middleware working
2. Add new features as event subscribers
3. Migrate middleware to events over time
4. Both systems work simultaneously

See MIGRATION.md for detailed migration guide.
