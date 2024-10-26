<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\Projects;

use App\Api\Services\Base\AbstractRequestValidator;
use App\Api\Services\Projects\ProjectsRequestValidator;
use App\Api\Services\ValidationWrapper;
use App\System\Testing\PhpUnit\DefaultTestCase;
use OpenAPI\Server\Model\UpdateProjectRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @internal
 */
#[CoversClass(ProjectsRequestValidator::class)]
final class ProjectsRequestValidatorTest extends DefaultTestCase
{
  protected MockObject|ProjectsRequestValidator $object;

  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(ProjectsRequestValidator::class)
      ->disableOriginalConstructor()
      ->onlyMethods([])
      ->getMock()
    ;
  }

  /**
   * @group unit
   *
   * @throws \ReflectionException
   */
  public function testValidateName(): void
  {
    $validation_wrapper = new ValidationWrapper();
    $this->mockProperty(AbstractRequestValidator::class, $this->object, 'validationWrapper', $validation_wrapper);

    $names = ['a', 'ab', 'abc', str_pad('a', 253, 'a'), str_pad('a', 254, 'a'), str_pad('a', 255, 'a')];

    foreach ($names as $name) {
      $this->invokeMethod($this->object, 'validateName', [$name, 'en']);
      $this->assertFalse($validation_wrapper->hasError());
      $validation_wrapper->clear();
    }
  }

  /**
   * @group unit
   *
   * @throws \ReflectionException
   * @throws Exception
   */
  public function testValidateNameEmpty(): void
  {
    $validation_wrapper = new ValidationWrapper();
    $this->mockProperty(AbstractRequestValidator::class, $this->object, 'validationWrapper', $validation_wrapper);
    $translator = $this->createMock(TranslatorInterface::class);
    $translator->method('trans')->with('api.project.nameEmpty', [], 'catroweb', 'en')->willReturn('EMPTY');
    $this->mockProperty(AbstractRequestValidator::class, $this->object, 'translator', $translator);

    $this->invokeMethod($this->object, 'validateName', ['', 'en']);
    $this->assertCount(1, $validation_wrapper->getErrors());
    $this->assertSame('EMPTY', $validation_wrapper->getError('name'));
  }

  /**
   * @group unit
   *
   * @throws \ReflectionException
   * @throws Exception
   */
  public function testValidateNameTooLong(): void
  {
    $validation_wrapper = new ValidationWrapper();
    $this->mockProperty(AbstractRequestValidator::class, $this->object, 'validationWrapper', $validation_wrapper);
    $translator = $this->createMock(TranslatorInterface::class);
    $translator->method('trans')->with('api.project.nameTooLong', [], 'catroweb', 'en')->willReturn('TOO LONG');
    $this->mockProperty(AbstractRequestValidator::class, $this->object, 'translator', $translator);

    $this->invokeMethod($this->object, 'validateName', [str_pad('a', 256, 'a'), 'en']);
    $this->assertCount(1, $validation_wrapper->getErrors());
    $this->assertSame('TOO LONG', $validation_wrapper->getError('name'));
  }

  /**
   * @group unit
   *
   * @throws \ReflectionException
   */
  public function testValidateDescription(): void
  {
    $validation_wrapper = new ValidationWrapper();
    $this->mockProperty(AbstractRequestValidator::class, $this->object, 'validationWrapper', $validation_wrapper);

    $names = ['', 'a', str_pad('a', 9_999, 'a'), str_pad('a', 10_000, 'a')];

    foreach ($names as $name) {
      $this->invokeMethod($this->object, 'validateDescription', [$name, 'en']);
      $this->assertFalse($validation_wrapper->hasError());
      $validation_wrapper->clear();
    }
  }

  /**
   * @group unit
   *
   * @throws \ReflectionException
   * @throws Exception
   */
  public function testValidateDescriptionTooLong(): void
  {
    $validation_wrapper = new ValidationWrapper();
    $this->mockProperty(AbstractRequestValidator::class, $this->object, 'validationWrapper', $validation_wrapper);
    $translator = $this->createMock(TranslatorInterface::class);
    $translator->method('trans')->with('api.project.descriptionTooLong', [], 'catroweb', 'en')->willReturn('TOO LONG');
    $this->mockProperty(AbstractRequestValidator::class, $this->object, 'translator', $translator);

    $this->invokeMethod($this->object, 'validateDescription', [str_pad('a', 10_001, 'a'), 'en']);
    $this->assertCount(1, $validation_wrapper->getErrors());
    $this->assertSame('TOO LONG', $validation_wrapper->getError('description'));
  }

  /**
   * @group unit
   *
   * @throws \ReflectionException
   */
  public function testValidateCredits(): void
  {
    $validation_wrapper = new ValidationWrapper();
    $this->mockProperty(AbstractRequestValidator::class, $this->object, 'validationWrapper', $validation_wrapper);

    $names = ['', 'a', str_pad('a', 2_999, 'a'), str_pad('a', 3_000, 'a')];

    foreach ($names as $name) {
      $this->invokeMethod($this->object, 'validateCredits', [$name, 'en']);
      $this->assertFalse($validation_wrapper->hasError());
      $validation_wrapper->clear();
    }
  }

  /**
   * @group unit
   *
   * @throws \ReflectionException
   * @throws Exception
   */
  public function testValidateCreditsTooLong(): void
  {
    $validation_wrapper = new ValidationWrapper();
    $this->mockProperty(AbstractRequestValidator::class, $this->object, 'validationWrapper', $validation_wrapper);
    $translator = $this->createMock(TranslatorInterface::class);
    $translator->method('trans')->with('api.project.creditsTooLong', [], 'catroweb', 'en')->willReturn('TOO LONG');
    $this->mockProperty(AbstractRequestValidator::class, $this->object, 'translator', $translator);

    $this->invokeMethod($this->object, 'validateCredits', [str_pad('a', 3_001, 'a'), 'en']);
    $this->assertCount(1, $validation_wrapper->getErrors());
    $this->assertSame('TOO LONG', $validation_wrapper->getError('credits'));
  }

  /**
   * @group unit
   *
   * @throws \ReflectionException
   */
  public function testValidateScreenshot(): void
  {
    $validation_wrapper = new ValidationWrapper();
    $this->mockProperty(AbstractRequestValidator::class, $this->object, 'validationWrapper', $validation_wrapper);

    $small_gif_base64 = 'R0lGODlhAQABAIABAP///wAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
    $screenshot = 'data:image/gif;base64,'.$small_gif_base64;

    $this->invokeMethod($this->object, 'validateScreenshot', [$screenshot, 'en']);
    $this->assertFalse($validation_wrapper->hasError());
  }

  /**
   * @group unit
   *
   * @throws \ReflectionException
   * @throws Exception
   */
  public function testValidateScreenshotInvalid(): void
  {
    $validation_wrapper = new ValidationWrapper();
    $this->mockProperty(AbstractRequestValidator::class, $this->object, 'validationWrapper', $validation_wrapper);
    $translator = $this->createMock(TranslatorInterface::class);
    $translator->method('trans')->with('api.project.screenshotInvalid', [], 'catroweb', 'en')->willReturn('INVALID');
    $this->mockProperty(AbstractRequestValidator::class, $this->object, 'translator', $translator);

    $screenshots = [
      '', // empty -> invalid regex
      'data:image/jpeg;base64,Q2F0cm93ZWI=', // invalid image, valid base64
      'data:image/png;base64,Catro/web', // invalid base64, valid regex
      'data:video/webm;base32,invalid', // invalid regex
    ];

    foreach ($screenshots as $screenshot) {
      $this->invokeMethod($this->object, 'validateScreenshot', [$screenshot, 'en']);
      $this->assertCount(1, $validation_wrapper->getErrors());
      $this->assertSame('INVALID', $validation_wrapper->getError('screenshot'));
      $validation_wrapper->clear();
    }
  }

  /**
   * @throws \ReflectionException
   */
  #[Group('unit')]
  public function testValidateUpdateRequest(): void
  {
    $validation_wrapper = new ValidationWrapper();
    $this->mockProperty(AbstractRequestValidator::class, $this->object, 'validationWrapper', $validation_wrapper);

    $small_gif_base64 = 'R0lGODlhAQABAIABAP///wAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
    $valid_screenshot = 'data:image/gif;base64,'.$small_gif_base64;

    $update_request = new UpdateProjectRequest([
      'name' => 'My project',
      'description' => 'An amazing Catrobat project',
      'credits' => '',
      'private' => false,
      'screenshot' => $valid_screenshot,
    ]);
    $this->object->validateUpdateRequest($update_request, 'en');
    $this->assertFalse($validation_wrapper->hasError());
  }

  /**
   * @throws \ReflectionException
   * @throws Exception
   */
  #[Group('unit')]
  public function testValidateUpdateRequestInvalid(): void
  {
    $validation_wrapper = new ValidationWrapper();
    $this->mockProperty(AbstractRequestValidator::class, $this->object, 'validationWrapper', $validation_wrapper);

    $translator = $this->createMock(TranslatorInterface::class);
    $translator->method('trans')->willReturnArgument(0);
    $this->mockProperty(AbstractRequestValidator::class, $this->object, 'translator', $translator);

    $update_request = new UpdateProjectRequest([
      'name' => '',
      'description' => str_pad('a', 10_001, 'a'),
      'credits' => str_pad('b', 3_001, 'b'),
      'private' => true,
      'screenshot' => 'invalid',
    ]);

    $this->object->validateUpdateRequest($update_request, 'en');
    $this->assertCount(4, $validation_wrapper->getErrors());
    $this->assertSame('api.project.nameEmpty', $validation_wrapper->getError('name'));
    $this->assertSame('api.project.descriptionTooLong', $validation_wrapper->getError('description'));
    $this->assertSame('api.project.creditsTooLong', $validation_wrapper->getError('credits'));
    $this->assertSame('api.project.screenshotInvalid', $validation_wrapper->getError('screenshot'));
  }
}
