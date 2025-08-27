<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Translation;

use App\DB\Entity\Project\Program;
use App\DB\EntityRepository\Translation\ProjectCustomTranslationRepository;
use App\DB\EntityRepository\Translation\ProjectMachineTranslationRepository;
use App\Translation\TranslationApiInterface;
use App\Translation\TranslationDelegate;
use App\Translation\TranslationResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TranslationDelegate::class)]
class TranslationDelegateTest extends TestCase
{
  private MockObject|ProjectCustomTranslationRepository $project_custom_translation_repository;

  private MockObject|ProjectMachineTranslationRepository $project_machine_translation_repository;

  /**
   * @throws Exception
   */
  #[\Override]
  protected function setUp(): void
  {
    $this->project_custom_translation_repository = $this->createMock(ProjectCustomTranslationRepository::class);
    $this->project_machine_translation_repository = $this->createMock(ProjectMachineTranslationRepository::class);
    $this->project_machine_translation_repository->method('getCachedTranslation')->willReturn(null);
  }

  /**
   * @throws Exception
   */
  public function testSingleApi(): void
  {
    $api = $this->createMock(TranslationApiInterface::class);
    $expected_result = new TranslationResult();
    $api->expects($this->once())
      ->method('translate')
      ->willReturn($expected_result)
    ;

    $translation_delegate = new TranslationDelegate($this->project_custom_translation_repository, $this->project_machine_translation_repository, $api);

    $actual_result = $translation_delegate->translate('test', 'en', 'fr');

    $this->assertEquals($expected_result, $actual_result);
  }

  /**
   * @throws Exception
   */
  public function testOrderApis(): void
  {
    $api1 = $this->createMock(TranslationApiInterface::class);
    $api2 = $this->createMock(TranslationApiInterface::class);

    $api1->method('getPreference')->willReturn(0.0);
    $api2->method('getPreference')->willReturn(1.0);

    $api1->expects($this->never())->method('translate');

    $expected_result = new TranslationResult();
    $api2->expects($this->once())
      ->method('translate')
      ->willReturn($expected_result)
    ;

    $translation_delegate = new TranslationDelegate($this->project_custom_translation_repository, $this->project_machine_translation_repository, $api1, $api2);

    $actual_result = $translation_delegate->translate('test', 'en', 'fr');

    $this->assertEquals($expected_result, $actual_result);
  }

  /**
   * @throws Exception
   */
  public function testTryNextApiIfFirstFails(): void
  {
    $api1 = $this->createMock(TranslationApiInterface::class);
    $api2 = $this->createMock(TranslationApiInterface::class);

    $api1->expects($this->once())
      ->method('translate')
      ->willReturn(null)
    ;

    $expected_result = new TranslationResult();
    $api2->expects($this->once())
      ->method('translate')
      ->willReturn($expected_result)
    ;

    $translation_delegate = new TranslationDelegate($this->project_custom_translation_repository, $this->project_machine_translation_repository, $api1, $api2);

    $actual_result = $translation_delegate->translate('test', 'en', 'fr');

    $this->assertEquals($expected_result, $actual_result);
  }

  /**
   * @throws Exception
   */
  public function testAllApiFails(): void
  {
    $api1 = $this->createMock(TranslationApiInterface::class);
    $api2 = $this->createMock(TranslationApiInterface::class);

    $api1->expects($this->once())
      ->method('translate')
      ->willReturn(null)
    ;

    $api2->expects($this->once())
      ->method('translate')
      ->willReturn(null)
    ;

    $translation_delegate = new TranslationDelegate($this->project_custom_translation_repository, $this->project_machine_translation_repository, $api1, $api2);

    $result = $translation_delegate->translate('test', 'en', 'fr');

    $this->assertNull($result);
  }

  #[DataProvider('provideInvalidLanguageCode')]
  public function testInvalidLanguageCode(string $invalid_code): void
  {
    $this->expectException(\InvalidArgumentException::class);
    $translation_delegate = new TranslationDelegate($this->project_custom_translation_repository, $this->project_machine_translation_repository);

    $translation_delegate->translate('test', $invalid_code, $invalid_code);
  }

  public static function provideInvalidLanguageCode(): array
  {
    return [
      [''],
      ['x'],
      ['xx'],
      ['EN'], // need to be lowercase en
      ['xxxxx'],
      ['en-XX'],
      ['EN-US'], // need to be lowercase en
      ['en-us'], // need to be uppercase US
      ['xx-US'],
    ];
  }

  /**
   * @throws Exception
   */
  public function testTranslateProjectWithOnlyName(): void
  {
    $api = $this->createMock(TranslationApiInterface::class);
    $translation_result = new TranslationResult();
    $api->expects($this->once())
      ->method('translate')
      ->willReturn($translation_result)
    ;

    $project = new Program();
    $project->setName('name');

    $translation_delegate = new TranslationDelegate($this->project_custom_translation_repository, $this->project_machine_translation_repository, $api);

    $actual_result = $translation_delegate->translateProject($project, 'en', 'fr');

    $this->assertEquals([$translation_result, null, null], $actual_result);
  }

  /**
   * @throws Exception
   */
  public function testTranslateProjectWithDescriptionAndCredit(): void
  {
    $api = $this->createMock(TranslationApiInterface::class);
    $translation_result = new TranslationResult();
    $api->expects($this->atLeastOnce())
      ->method('translate')
      ->willReturn($translation_result)
    ;

    $project = new Program();
    $project->setName('name');
    $project->setDescription('description');
    $project->setCredits('credit');

    $translation_delegate = new TranslationDelegate($this->project_custom_translation_repository, $this->project_machine_translation_repository, $api);

    $actual_result = $translation_delegate->translateProject($project, 'en', 'fr');

    $this->assertEquals([$translation_result, $translation_result, $translation_result], $actual_result);
  }

  /**
   * @throws Exception
   */
  public function testTranslateProjectFailure(): void
  {
    $api = $this->createMock(TranslationApiInterface::class);
    $api->expects($this->atLeastOnce())
      ->method('translate')
      ->willReturn(null)
    ;

    $project = new Program();
    $project->setName('name');
    $project->setDescription('description');
    $project->setCredits('credit');

    $translation_delegate = new TranslationDelegate($this->project_custom_translation_repository, $this->project_machine_translation_repository, $api);

    $actual_result = $translation_delegate->translateProject($project, 'en', 'fr');

    $this->assertNull($actual_result);
  }

  public function testAddProjectCustomTranslation(): void
  {
    $translation_delegate = new TranslationDelegate($this->project_custom_translation_repository, $this->project_machine_translation_repository);
    $project = new Program();

    $this->project_custom_translation_repository->expects($this->once())
      ->method('addNameTranslation')
      ->with($project, 'fr', 'test')
      ->willReturn(true)
    ;

    $this->project_custom_translation_repository->expects($this->once())
      ->method('addDescriptionTranslation')
      ->with($project, 'fr', 'test')
      ->willReturn(true)
    ;

    $this->project_custom_translation_repository->expects($this->once())
      ->method('addCreditTranslation')
      ->with($project, 'fr', 'test')
      ->willReturn(true)
    ;

    $this->assertTrue(
      $translation_delegate->addProjectNameCustomTranslation($project, 'fr', 'test'));
    $this->assertTrue(
      $translation_delegate->addProjectDescriptionCustomTranslation($project, 'fr', 'test'));
    $this->assertTrue(
      $translation_delegate->addProjectCreditCustomTranslation($project, 'fr', 'test'));
  }

  /**
   * @throws Exception
   */
  public function testCachedProjectTranslation(): void
  {
    $cached_translation = [new TranslationResult(), null, null];
    $this->project_machine_translation_repository = $this->createMock(ProjectMachineTranslationRepository::class);
    $this->project_machine_translation_repository->method('getCachedTranslation')->willReturn($cached_translation);

    $api = $this->createMock(TranslationApiInterface::class);

    $translation_delegate = new TranslationDelegate($this->project_custom_translation_repository, $this->project_machine_translation_repository, $api);

    $actual_result = $translation_delegate->translateProject(new Program(), 'en', 'fr');

    $this->assertEquals($cached_translation, $actual_result);
  }
}
