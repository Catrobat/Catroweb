<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\User;

use App\Api\Services\User\UserRequestValidator;
use App\Api\Services\ValidationWrapper;
use App\Security\ContentSafety\ContentSafetyScanner;
use App\User\UserManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @internal
 *
 * @covers \App\Api\Services\User\UserRequestValidator
 */
class UserRequestValidatorDateOfBirthTest extends TestCase
{
  private UserRequestValidator $validator;

  #[\Override]
  protected function setUp(): void
  {
    $symfonyValidator = $this->createStub(ValidatorInterface::class);
    $symfonyValidator->method('validate')->willReturn(new ConstraintViolationList());
    $translator = $this->createStub(TranslatorInterface::class);
    $translator->method('trans')->willReturnCallback(fn (string $id) => $id);
    $userManager = $this->createStub(UserManager::class);
    $logger = $this->createStub(LoggerInterface::class);
    $passwordHasherFactory = $this->createStub(PasswordHasherFactoryInterface::class);
    $cache = $this->createStub(CacheInterface::class);
    $cache->method('get')->willReturn(['com' => true, 'org' => true, 'net' => true, 'at' => true]);
    $contentSafetyScanner = $this->createStub(ContentSafetyScanner::class);

    $this->validator = new UserRequestValidator(
      $symfonyValidator,
      $translator,
      $userManager,
      $passwordHasherFactory,
      $cache,
      $logger,
      $contentSafetyScanner,
    );
  }

  #[DataProvider('validAdultDateOfBirthProvider')]
  public function testValidAdultDateOfBirth(string $dob): void
  {
    $request = $this->createRegistrationRequest($dob);
    $result = $this->validator->validateRegistration($request, 'en');

    $this->assertFalse(
      $result->hasError(),
      sprintf('DOB "%s" should be valid but got errors: %s', $dob, implode(', ', $this->getErrorMessages($result)))
    );
  }

  public static function validAdultDateOfBirthProvider(): array
  {
    $today = new \DateTimeImmutable('today');

    return [
      '14 years old' => [$today->modify('-14 years')->format('Y-m-d')],
      '18 years old' => [$today->modify('-18 years')->format('Y-m-d')],
      '30 years old' => ['1996-06-15'],
      '80 years old' => ['1946-01-01'],
    ];
  }

  #[DataProvider('validMinorDateOfBirthProvider')]
  public function testValidMinorDateOfBirthWithParentEmail(string $dob): void
  {
    $request = $this->createRegistrationRequest($dob, 'parent@example.com');
    $result = $this->validator->validateRegistration($request, 'en');

    $this->assertFalse(
      $result->hasError(),
      sprintf('DOB "%s" with parent email should be valid but got errors: %s', $dob, implode(', ', $this->getErrorMessages($result)))
    );
  }

  #[DataProvider('validMinorDateOfBirthProvider')]
  public function testMinorDateOfBirthWithoutParentEmailFails(string $dob): void
  {
    $request = $this->createRegistrationRequest($dob);
    $result = $this->validator->validateRegistration($request, 'en');

    $this->assertTrue($result->hasError(), sprintf('DOB "%s" without parent email should fail', $dob));
  }

  public static function validMinorDateOfBirthProvider(): array
  {
    $today = new \DateTimeImmutable('today');

    return [
      'exactly 3 years old' => [$today->modify('-3 years')->format('Y-m-d')],
      '5 years old' => [$today->modify('-5 years')->format('Y-m-d')],
      '10 years old' => [$today->modify('-10 years')->format('Y-m-d')],
      '13 years old' => [$today->modify('-13 years')->format('Y-m-d')],
    ];
  }

  public function testParentEmailSameAsUserEmailFails(): void
  {
    $request = new \OpenAPI\Server\Model\RegisterRequest([
      'email' => 'child@example.com',
      'username' => 'ChildUser',
      'password' => '123456',
      'date_of_birth' => (new \DateTimeImmutable('today'))->modify('-10 years')->format('Y-m-d'),
      'parent_email' => 'child@example.com',
    ]);
    $result = $this->validator->validateRegistration($request, 'en');

    $this->assertTrue($result->hasError(), 'Parent email same as user email should fail');
  }

  public function testParentEmailSameAsUserEmailCaseInsensitive(): void
  {
    $request = new \OpenAPI\Server\Model\RegisterRequest([
      'email' => 'child@example.com',
      'username' => 'ChildUser',
      'password' => '123456',
      'date_of_birth' => (new \DateTimeImmutable('today'))->modify('-10 years')->format('Y-m-d'),
      'parent_email' => 'CHILD@EXAMPLE.COM',
    ]);
    $result = $this->validator->validateRegistration($request, 'en');

    $this->assertTrue($result->hasError(), 'Parent email same as user email (case-insensitive) should fail');
  }

  #[DataProvider('invalidDateOfBirthProvider')]
  public function testInvalidDateOfBirth(string $dob): void
  {
    $request = $this->createRegistrationRequest($dob);
    $result = $this->validator->validateRegistration($request, 'en');

    $this->assertTrue($result->hasError(), sprintf('DOB "%s" should be invalid', $dob));
  }

  public static function invalidDateOfBirthProvider(): array
  {
    $today = new \DateTimeImmutable('today');

    return [
      'under 3 years (2 years)' => [$today->modify('-2 years')->format('Y-m-d')],
      'future date' => [$today->modify('+1 year')->format('Y-m-d')],
      'invalid format' => ['not-a-date'],
      'partial date' => ['2000-13-01'],
      'empty string' => [''],
    ];
  }

  public function testMissingDateOfBirth(): void
  {
    $request = $this->createRegistrationRequest(null);
    $result = $this->validator->validateRegistration($request, 'en');

    $this->assertTrue($result->hasError());
  }

  private function createRegistrationRequest(?string $dob, ?string $parentEmail = null): \OpenAPI\Server\Model\RegisterRequest
  {
    $data = [
      'email' => 'test@example.com',
      'username' => 'TestUser',
      'password' => '123456',
      'date_of_birth' => $dob,
    ];

    if (null !== $parentEmail) {
      $data['parent_email'] = $parentEmail;
    }

    return new \OpenAPI\Server\Model\RegisterRequest($data);
  }

  private function getErrorMessages(ValidationWrapper $wrapper): array
  {
    $reflection = new \ReflectionClass($wrapper);
    $prop = $reflection->getProperty('errors');

    return array_map(fn ($e) => $e['message'] ?? $e, $prop->getValue($wrapper));
  }
}
