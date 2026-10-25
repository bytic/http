# Contributing to ByTIC HTTP

Thank you for considering contributing to the ByTIC HTTP package! This guide will help you understand how to contribute effectively.

## Development Principles

This package follows these core principles:

1. **Backward Compatibility** - Never break existing functionality
2. **Symfony Compliance** - Align with Symfony's HTTP kernel architecture
3. **Clean Code** - Follow PSR-12 coding standards
4. **Test Coverage** - All new features should have tests
5. **Documentation** - Document all public APIs

## Getting Started

### Prerequisites

- PHP 8.2 or higher
- Composer
- Git

### Setup

1. Fork the repository
2. Clone your fork:
   ```bash
   git clone https://github.com/your-username/http.git
   cd http
   ```

3. Install dependencies:
   ```bash
   composer install
   ```

4. Create a feature branch:
   ```bash
   git checkout -b feature/my-new-feature
   ```

## Development Workflow

### 1. Code Changes

- Write clean, readable code following PSR-12
- Add PHPDoc comments for all public methods
- Follow existing code style and patterns
- Keep changes focused and minimal

### 2. Testing

Run the test suite:

```bash
composer test
# or
./vendor/bin/phpunit
```

Add tests for new features:

```bash
# Create test file in tests/src/
tests/src/YourFeature/YourFeatureTest.php
```

Test structure:
```php
<?php

namespace Nip\Http\Tests\YourFeature;

use Nip\Http\Tests\AbstractTest;

class YourFeatureTest extends AbstractTest
{
    public function testYourFeature()
    {
        // Arrange
        $object = new YourClass();
        
        // Act
        $result = $object->yourMethod();
        
        // Assert
        $this->assertEquals('expected', $result);
    }
}
```

### 3. Code Quality

Run code quality tools:

```bash
# PHP CodeSniffer
composer cs-check

# PHP Stan
composer phpstan

# Fix code style issues
composer cs-fix
```

### 4. Documentation

Update documentation for your changes:

- **README.md** - For user-facing features
- **MIGRATION.md** - For breaking changes or migration paths
- **CHANGELOG.md** - Document all changes
- **PHPDoc** - Inline code documentation

### 5. Commit Messages

Follow conventional commits:

```
feat: add support for custom event subscribers
fix: resolve issue with request stack in sub-requests
docs: update README with event examples
test: add tests for ControllerResolver
refactor: simplify event dispatching logic
```

Types:
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `test`: Test additions or changes
- `refactor`: Code refactoring
- `perf`: Performance improvements
- `style`: Code style changes

## Adding New Features

### Event System

When adding new events:

1. Create event class in `src/Kernel/Event/`
2. Extend `KernelEvent` or appropriate base class
3. Add event constant to `KernelEvents`
4. Dispatch event in appropriate kernel method
5. Add example subscriber
6. Update documentation

Example:
```php
// 1. Create event
class MyEvent extends KernelEvent
{
    protected $data;
    
    public function getData() { return $this->data; }
    public function setData($data) { $this->data = $data; }
}

// 2. Add constant
class KernelEvents
{
    const MY_EVENT = 'kernel.my_event';
}

// 3. Dispatch in kernel
$event = new MyEvent($request, $type);
$this->dispatchEvent($event, KernelEvents::MY_EVENT);
```

### Middleware

When adding middleware support:

1. Implement `ServerMiddlewareInterface` from PSR-15
2. Add to default middleware stack or middleware groups
3. Provide event subscriber alternative
4. Document both approaches

### Request/Response Features

When enhancing Request or Response:

1. Extend Symfony's base classes
2. Add traits for reusable functionality
3. Maintain PSR-7 compatibility where needed
4. Document differences from Symfony

## Pull Request Process

1. **Update Documentation**
   - README.md for user-facing changes
   - CHANGELOG.md for all changes
   - MIGRATION.md for breaking changes
   - PHPDoc for code changes

2. **Run All Checks**
   ```bash
   composer test
   composer cs-check
   composer phpstan
   ```

3. **Create Pull Request**
   - Clear title describing the change
   - Description of what changed and why
   - Reference any related issues
   - Include examples if applicable

4. **Code Review**
   - Address review feedback
   - Keep PR focused and small
   - Rebase on main if needed

5. **Merge**
   - Squash commits if requested
   - Update branch if needed
   - Wait for maintainer approval

## Code Style Guidelines

### PSR-12 Compliance

Follow PSR-12 coding standards:
- 4 spaces for indentation
- Opening braces on new line for classes/methods
- One blank line after namespace
- One class per file

### Naming Conventions

- **Classes**: PascalCase (`RequestEvent`, `ControllerResolver`)
- **Methods**: camelCase (`getRequest()`, `setResponse()`)
- **Properties**: camelCase (`$requestType`, `$middleware`)
- **Constants**: UPPER_CASE (`MAIN_REQUEST`, `REQUEST`)

### PHPDoc

Always add PHPDoc comments:

```php
/**
 * Short description of what the method does.
 *
 * Longer description if needed. Explain behavior,
 * edge cases, and important details.
 *
 * @param Request $request The HTTP request
 * @param int $type The request type
 * @return Response The HTTP response
 * @throws \RuntimeException If something goes wrong
 */
public function handle(Request $request, int $type): Response
{
    // Implementation
}
```

## Backward Compatibility

**Critical**: Never break backward compatibility without major version bump.

### What NOT to break:
- Public method signatures
- Public property types
- Event names and structures
- Middleware interfaces
- Configuration formats

### How to deprecate:
```php
/**
 * @deprecated since version 2.1, use newMethod() instead
 */
public function oldMethod()
{
    trigger_error(
        'Method oldMethod() is deprecated, use newMethod() instead',
        E_USER_DEPRECATED
    );
    
    return $this->newMethod();
}
```

## Questions or Issues?

- **Questions**: Open a discussion on GitHub
- **Bugs**: Open an issue with reproduction steps
- **Features**: Open an issue to discuss before implementing
- **Security**: Email security@bytic.ro (do not open public issue)

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

## Thank You!

Your contributions make this project better for everyone. Thank you for taking the time to contribute!
