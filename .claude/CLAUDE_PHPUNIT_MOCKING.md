# PHPUnit Testing Best Practices - Mocks vs Stubs

## Overview

This document explains proper usage of test doubles in PHPUnit tests. PHPUnit 12+ strictly enforces best practices to help developers write clearer, more maintainable tests.

## The Problem

You may see this warning:

```
No expectations were configured for the mock object for ClassName.
Consider refactoring your test code to use a test stub instead.
```

This means you're using `createMock()` but not actually verifying behavior with `expects()`. This is a code smell - you should use `createStub()` instead.

## Mocks vs Stubs - When to Use Which

### Use `createStub()` when:

- You need a dependency to return specific values
- You're **NOT** verifying that methods were called
- The dependency is just providing data for the test
- You only care about **WHAT** is returned, not **HOW** it's called

### Use `createMock()` when:

- You're **verifying behavior** (that specific methods are called)
- You use `expects()` to assert interactions
- The test is about **HOW** the code interacts with dependencies
- You care about **method calls, arguments, and order**

## Common Patterns

### Pattern 1: Repository as Stub (No Behavior Verification)

```php
use PHPUnit\Framework\MockObject\Stub;

class UserServiceTest extends TestCase
{
  private Stub|UserRepository $repository;

  protected function setUp(): void
  {
    // GOOD - Using a stub for dependencies that just return data
    $this->repository = $this->createStub(UserRepository::class);
    $this->repository->method('find')->willReturn(new User());
    $this->service = new UserService($this->repository);
  }

  public function testGetUserName(): void
  {
    // We don't care HOW the repository was called, just that we get data
    $name = $this->service->getUserName(123);
    $this->assertEquals('John', $name);
  }
}
```

### Pattern 2: Mock When Verifying Behavior

```php
use PHPUnit\Framework\MockObject\MockObject;

class UserServiceTest extends TestCase
{
  public function testUserIsSaved(): void
  {
    // GOOD - Using a mock to verify the method was called correctly
    $repository = $this->createMock(UserRepository::class);
    $repository->expects($this->once())  // ← This is WHY we use createMock()
      ->method('save')
      ->with($this->isInstanceOf(User::class));

    $service = new UserService($repository);
    $service->createUser('John');

    // The assertion happens via expects() - we're testing BEHAVIOR
  }
}
```

### Pattern 3: Mixed Approach (Stub by Default, Mock When Needed)

```php
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\MockObject\MockObject;

class TranslateClientTest extends TestCase
{
  private Stub|TranslateClient $client;
  private GoogleTranslateApi $api;

  protected function setUp(): void
  {
    // Stub by default - many tests don't need to verify behavior
    $this->client = $this->createStub(TranslateClient::class);
    $this->api = new GoogleTranslateApi($this->client, $this->createStub(LoggerInterface::class), 5);
  }

  public function testTranslatesText(): void
  {
    // For this test, we need to verify the client is called correctly
    $client = $this->createMock(TranslateClient::class);
    $client->expects($this->once())
      ->method('translate')
      ->with('hello', ['target' => 'fr'])
      ->willReturn(['text' => 'bonjour']);

    $api = new GoogleTranslateApi($client, $this->createStub(LoggerInterface::class), 5);
    $result = $api->translate('hello', null, 'fr');

    $this->assertEquals('bonjour', $result->translation);
  }

  public function testGetPreference(): void
  {
    // This test doesn't call the client at all, so the stub from setUp() is fine
    $preference = $this->api->getPreference('test', 'en', 'fr');
    $this->assertEquals(1.0, $preference);
  }
}
```

### Pattern 4: Type Hints Show Intent

```php
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;

class MyTest extends TestCase
{
  // Clear signal: this is a stub (returns values, no behavior verification)
  private Stub|UserRepository $userRepository;
  private Stub|LoggerInterface $logger;

  // Clear signal: these are mocks (behavior will be verified)
  private MockObject|EmailService $emailService;
  private MockObject|EventDispatcher $eventDispatcher;

  protected function setUp(): void
  {
    // Stubs - just return values
    $this->userRepository = $this->createStub(UserRepository::class);
    $this->logger = $this->createStub(LoggerInterface::class);

    // Mocks - we'll use expects() on these
    $this->emailService = $this->createMock(EmailService::class);
    $this->eventDispatcher = $this->createMock(EventDispatcher::class);
  }
}
```

## What NOT to Do

### ❌ BAD: Creating mocks without expectations

```php
// BAD - createMock() without expects()
protected function setUp(): void
{
  $this->logger = $this->createMock(LoggerInterface::class);  // ← Wrong!
  $this->repository = $this->createMock(UserRepository::class);  // ← Wrong!
}

public function testSomething(): void
{
  // No expects() anywhere - these should be stubs!
  $result = $this->service->doSomething();
  $this->assertTrue($result);
}
```

### ❌ BAD: Using opt-out attribute instead of proper refactoring

```php
// BAD - This hides the problem instead of fixing it
#[AllowMockObjectsWithoutExpectations]
public function testSomething(): void
{
  $logger = $this->createMock(LoggerInterface::class);  // ← Should be createStub()!
  // ...
}
```

### ✅ GOOD: Use the right tool for the job

```php
// GOOD - Using stubs for dependencies that just return values
protected function setUp(): void
{
  $this->logger = $this->createStub(LoggerInterface::class);  // ← Correct!
  $this->repository = $this->createStub(UserRepository::class);  // ← Correct!
}

public function testSomething(): void
{
  $result = $this->service->doSomething();
  $this->assertTrue($result);
}
```

## Refactoring Checklist

When you see PHPUnit warnings about mocks without expectations:

1. **Identify the dependency** - What object is triggering the warning?

2. **Search for `expects()` in the test file**
   - If NO `expects()` anywhere → Change ALL `createMock()` to `createStub()`
   - If `expects()` in SOME tests → Use Pattern 3 (stub by default, mock in specific tests)

3. **Update type hints**

   ```php
   // From:
   private MockObject|Logger $logger;

   // To:
   private Stub|Logger $logger;
   ```

4. **Update imports** if needed

   ```php
   use PHPUnit\Framework\MockObject\Stub;
   ```

5. **Run tests** to verify everything still passes

6. **Remove any `#[AllowMockObjectsWithoutExpectations]` attributes**

## Examples from the Codebase

### Example 1: TranslationDelegateTest (Properly Refactored)

**Before:**

```php
private MockObject|ProjectCustomTranslationRepository $repository;

protected function setUp(): void
{
  $this->repository = $this->createMock(ProjectCustomTranslationRepository::class);
}

#[AllowMockObjectsWithoutExpectations]
public function testTranslate(): void
{
  // No expects() used
  $result = $this->delegate->translate('test', 'en', 'fr');
  $this->assertNotNull($result);
}
```

**After:**

```php
private Stub|ProjectCustomTranslationRepository $repository;

protected function setUp(): void
{
  $this->repository = $this->createStub(ProjectCustomTranslationRepository::class);
}

public function testTranslate(): void
{
  // Clean - no warning, clear intent
  $result = $this->delegate->translate('test', 'en', 'fr');
  $this->assertNotNull($result);
}

public function testAddTranslation(): void
{
  // For this test, we need to verify behavior
  $repository = $this->createMock(ProjectCustomTranslationRepository::class);
  $repository->expects($this->once())
    ->method('addNameTranslation')
    ->with($project, 'fr', 'test');

  $delegate = new TranslationDelegate($repository, ...);
  $delegate->addProjectNameCustomTranslation($project, 'fr', 'test');
}
```

### Example 2: GoogleTranslateApiTest (Properly Refactored)

**Before:**

```php
private MockObject|TranslateClient $client;

protected function setUp(): void
{
  $this->client = $this->createMock(TranslateClient::class);
  $this->api = new GoogleTranslateApi($this->client, ...);
}

#[AllowMockObjectsWithoutExpectations]
public function testGetPreference(): void
{
  // Doesn't use $this->client at all!
  $this->assertEquals(1.0, $this->api->getPreference('test', 'en', 'fr'));
}
```

**After:**

```php
private Stub|TranslateClient $client;

protected function setUp(): void
{
  $this->client = $this->createStub(TranslateClient::class);
  $this->api = new GoogleTranslateApi($this->client, ...);
}

public function testGetPreference(): void
{
  // Clean - no warning
  $this->assertEquals(1.0, $this->api->getPreference('test', 'en', 'fr'));
}

public function testTranslate(): void
{
  // Create a mock when we need to verify behavior
  $client = $this->createMock(TranslateClient::class);
  $client->expects($this->once())->method('translate')->willReturn(['text' => 'bonjour']);
  $api = new GoogleTranslateApi($client, ...);

  $result = $api->translate('hello', null, 'fr');
  $this->assertEquals('bonjour', $result->translation);
}
```

## Why This Matters

1. **Test Clarity** - Stubs vs mocks makes intent explicit and tests easier to understand
2. **Maintainability** - Future developers immediately understand what's being tested
3. **Best Practices** - Aligns with modern PHP/PHPUnit standards
4. **Faster Tests** - Stubs are lighter weight than mocks

## Summary

- **Stub** = "I need this dependency to return a value"
- **Mock** = "I need to verify this dependency was used correctly"

When in doubt, start with a stub. Only upgrade to a mock when you need to verify behavior with `expects()`.

**Never use `#[AllowMockObjectsWithoutExpectations]` - it's a code smell that hides the real issue.**
