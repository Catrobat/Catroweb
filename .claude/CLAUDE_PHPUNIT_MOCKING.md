# PHPUnit Test Doubles: Mocks vs Stubs

PHPUnit 13 warns "No expectations were configured for the mock object" when `createMock()` is used without `expects()`. Fix the test, don't silence it.

## Rules

- `createStub()` when the dependency only returns values. `createMock()` only when the test asserts interactions with `expects()`.
- Never `#[AllowMockObjectsWithoutExpectations]`; it hides the problem.
- Don't use `expects($this->any())`, and don't use `with()` without `expects()`.
- Stub by default in `setUp()`; create a local `createMock()` only in the tests that verify behavior.
- Type hints show intent: `Stub|UserRepository` for stubs, `MockObject|EmailService` for mocks (`use PHPUnit\Framework\MockObject\Stub;`).

```php
protected function setUp(): void
{
  $this->repository = $this->createStub(UserRepository::class);
  $this->repository->method('find')->willReturn(new User());
}

public function testUserIsSaved(): void
{
  $repository = $this->createMock(UserRepository::class);
  $repository->expects($this->once())->method('save')->with($this->isInstanceOf(User::class));
  (new UserService($repository))->createUser('John');
}
```

## Refactoring a warning

1. Find the double that triggers it.
2. No `expects()` anywhere in the file → change every `createMock()` to `createStub()`. Some tests use `expects()` → stub in `setUp()`, local mock in those tests.
3. Update type hints and imports, remove any `#[AllowMockObjectsWithoutExpectations]`, rerun the tests.
