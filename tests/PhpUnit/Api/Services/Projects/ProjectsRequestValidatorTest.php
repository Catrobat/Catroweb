<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\Projects;

use App\Api\Services\Projects\ProjectsRequestValidator;
use App\User\UserManager;
use OpenAPI\Server\Model\UpdateProjectRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @internal
 */
#[CoversClass(ProjectsRequestValidator::class)]
final class ProjectsRequestValidatorTest extends TestCase
{
  private ProjectsRequestValidator $validator;

  #[\Override]
  protected function setUp(): void
  {
    $symfony_validator = $this->createStub(ValidatorInterface::class);
    $translator = $this->createStub(TranslatorInterface::class);
    $translator->method('trans')->willReturnArgument(0);
    $user_manager = $this->createStub(UserManager::class);

    $this->validator = new ProjectsRequestValidator($symfony_validator, $translator, $user_manager);
  }

  /**
   * @group unit
   */
  #[DataProvider('validNameProvider')]
  public function testValidateUpdateRequestWithValidName(string $name): void
  {
    $small_gif_base64 = 'R0lGODlhAQABAIABAP///wAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
    $valid_screenshot = 'data:image/gif;base64,'.$small_gif_base64;

    $request = new UpdateProjectRequest([
      'name' => $name,
      'description' => 'Valid description',
      'credits' => '',
      'private' => false,
      'screenshot' => $valid_screenshot,
    ]);

    $result = $this->validator->validateUpdateRequest($request, 'en');
    $this->assertFalse($result->hasError(), "Name '{$name}' should be valid");
  }

  public static function validNameProvider(): array
  {
    return [
      'single char' => ['a'],
      'two chars' => ['ab'],
      'three chars' => ['abc'],
      'max minus 2' => [str_pad('a', 253, 'a')],
      'max minus 1' => [str_pad('a', 254, 'a')],
      'max length' => [str_pad('a', 255, 'a')],
    ];
  }

  /**
   * @group unit
   */
  public function testValidateUpdateRequestWithEmptyName(): void
  {
    $request = new UpdateProjectRequest([
      'name' => '',
      'description' => 'Valid description',
      'credits' => '',
      'private' => false,
    ]);

    $result = $this->validator->validateUpdateRequest($request, 'en');
    $this->assertTrue($result->hasError());
    $this->assertSame('api.project.nameEmpty', $result->getError('name'));
  }

  /**
   * @group unit
   */
  public function testValidateUpdateRequestWithNameTooLong(): void
  {
    $request = new UpdateProjectRequest([
      'name' => str_pad('a', 256, 'a'),
      'description' => 'Valid description',
      'credits' => '',
      'private' => false,
    ]);

    $result = $this->validator->validateUpdateRequest($request, 'en');
    $this->assertTrue($result->hasError());
    $this->assertSame('api.project.nameTooLong', $result->getError('name'));
  }

  /**
   * @group unit
   */
  #[DataProvider('validDescriptionProvider')]
  public function testValidateUpdateRequestWithValidDescription(string $description): void
  {
    $small_gif_base64 = 'R0lGODlhAQABAIABAP///wAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
    $valid_screenshot = 'data:image/gif;base64,'.$small_gif_base64;

    $request = new UpdateProjectRequest([
      'name' => 'Valid name',
      'description' => $description,
      'credits' => '',
      'private' => false,
      'screenshot' => $valid_screenshot,
    ]);

    $result = $this->validator->validateUpdateRequest($request, 'en');
    $this->assertFalse($result->hasError());
  }

  public static function validDescriptionProvider(): array
  {
    return [
      'empty' => [''],
      'single char' => ['a'],
      'max minus 1' => [str_pad('a', 9_999, 'a')],
      'max length' => [str_pad('a', 10_000, 'a')],
    ];
  }

  /**
   * @group unit
   */
  public function testValidateUpdateRequestWithDescriptionTooLong(): void
  {
    $request = new UpdateProjectRequest([
      'name' => 'Valid name',
      'description' => str_pad('a', 10_001, 'a'),
      'credits' => '',
      'private' => false,
    ]);

    $result = $this->validator->validateUpdateRequest($request, 'en');
    $this->assertTrue($result->hasError());
    $this->assertSame('api.project.descriptionTooLong', $result->getError('description'));
  }

  /**
   * @group unit
   */
  #[DataProvider('validCreditsProvider')]
  public function testValidateUpdateRequestWithValidCredits(string $credits): void
  {
    $small_gif_base64 = 'R0lGODlhAQABAIABAP///wAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
    $valid_screenshot = 'data:image/gif;base64,'.$small_gif_base64;

    $request = new UpdateProjectRequest([
      'name' => 'Valid name',
      'description' => 'Valid description',
      'credits' => $credits,
      'private' => false,
      'screenshot' => $valid_screenshot,
    ]);

    $result = $this->validator->validateUpdateRequest($request, 'en');
    $this->assertFalse($result->hasError());
  }

  public static function validCreditsProvider(): array
  {
    return [
      'empty' => [''],
      'single char' => ['a'],
      'max minus 1' => [str_pad('a', 2_999, 'a')],
      'max length' => [str_pad('a', 3_000, 'a')],
    ];
  }

  /**
   * @group unit
   */
  public function testValidateUpdateRequestWithCreditsTooLong(): void
  {
    $request = new UpdateProjectRequest([
      'name' => 'Valid name',
      'description' => 'Valid description',
      'credits' => str_pad('a', 3_001, 'a'),
      'private' => false,
    ]);

    $result = $this->validator->validateUpdateRequest($request, 'en');
    $this->assertTrue($result->hasError());
    $this->assertSame('api.project.creditsTooLong', $result->getError('credits'));
  }

  /**
   * @group unit
   */
  public function testValidateUpdateRequestWithValidScreenshot(): void
  {
    $small_gif_base64 = 'R0lGODlhAQABAIABAP///wAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
    $screenshot = 'data:image/gif;base64,'.$small_gif_base64;

    $request = new UpdateProjectRequest([
      'name' => 'Valid name',
      'description' => 'Valid description',
      'credits' => '',
      'private' => false,
      'screenshot' => $screenshot,
    ]);

    $result = $this->validator->validateUpdateRequest($request, 'en');
    $this->assertFalse($result->hasError());
  }

  /**
   * @group unit
   */
  #[DataProvider('invalidScreenshotProvider')]
  public function testValidateUpdateRequestWithInvalidScreenshot(string $screenshot, string $description): void
  {
    $request = new UpdateProjectRequest([
      'name' => 'Valid name',
      'description' => 'Valid description',
      'credits' => '',
      'private' => false,
      'screenshot' => $screenshot,
    ]);

    $result = $this->validator->validateUpdateRequest($request, 'en');
    $this->assertTrue($result->hasError(), "Screenshot should be invalid: {$description}");
    $this->assertSame('api.project.screenshotInvalid', $result->getError('screenshot'));
  }

  public static function invalidScreenshotProvider(): array
  {
    return [
      'empty string' => ['', 'empty -> invalid regex'],
      'invalid image data' => ['data:image/jpeg;base64,Q2F0cm93ZWI=', 'invalid image, valid base64'],
      'invalid base64' => ['data:image/png;base64,Catro/web', 'invalid base64, valid regex'],
      'invalid format' => ['data:video/webm;base32,invalid', 'invalid regex'],
    ];
  }

  /**
   * @group unit
   */
  public function testValidateUpdateRequestWithAllValidFields(): void
  {
    $small_gif_base64 = 'R0lGODlhAQABAIABAP///wAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
    $valid_screenshot = 'data:image/gif;base64,'.$small_gif_base64;

    $request = new UpdateProjectRequest([
      'name' => 'My project',
      'description' => 'An amazing Catrobat project',
      'credits' => '',
      'private' => false,
      'screenshot' => $valid_screenshot,
    ]);

    $result = $this->validator->validateUpdateRequest($request, 'en');
    $this->assertFalse($result->hasError());
  }

  /**
   * @group unit
   */
  public function testValidateUpdateRequestWithMultipleInvalidFields(): void
  {
    $request = new UpdateProjectRequest([
      'name' => '',
      'description' => str_pad('a', 10_001, 'a'),
      'credits' => str_pad('b', 3_001, 'b'),
      'private' => true,
      'screenshot' => 'invalid',
    ]);

    $result = $this->validator->validateUpdateRequest($request, 'en');
    $this->assertCount(4, $result->getErrors());
    $this->assertSame('api.project.nameEmpty', $result->getError('name'));
    $this->assertSame('api.project.descriptionTooLong', $result->getError('description'));
    $this->assertSame('api.project.creditsTooLong', $result->getError('credits'));
    $this->assertSame('api.project.screenshotInvalid', $result->getError('screenshot'));
  }
}
