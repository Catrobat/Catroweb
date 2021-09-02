<?php

namespace Translation;

use App\Entity\Program;
use App\Repository\ProjectCustomTranslationRepository;
use App\Translation\TranslationApiInterface;
use App\Translation\TranslationDelegate;
use App\Translation\TranslationResult;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \App\Translation\TranslationDelegate
 */
class TranslationDelegateTest extends TestCase
{
  /**
   * @var MockObject|ProjectCustomTranslationRepository
   */
  private $repository;

  protected function setUp(): void
  {
    $this->repository = $this->createMock(ProjectCustomTranslationRepository::class);
  }

  public function testSingleApi(): void
  {
    $api = $this->createMock(TranslationApiInterface::class);
    $expected_result = new TranslationResult();
    $api->expects($this->once())
      ->method('translate')
      ->willReturn($expected_result)
    ;

    $translation_delegate = new TranslationDelegate($this->repository, $api);

    $actual_result = $translation_delegate->translate('test', 'en', 'fr');

    $this->assertEquals($expected_result, $actual_result);
  }

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

    $translation_delegate = new TranslationDelegate($this->repository, $api1, $api2);

    $actual_result = $translation_delegate->translate('test', 'en', 'fr');

    $this->assertEquals($expected_result, $actual_result);
  }

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

    $translation_delegate = new TranslationDelegate($this->repository, $api1, $api2);

    $result = $translation_delegate->translate('test', 'en', 'fr');

    $this->assertNull($result);
  }

  /**
   * @dataProvider provideInvalidLanguageCode
   */
  public function testInvalidLanguageCode(string $invalid_code): void
  {
    $this->expectException(InvalidArgumentException::class);
    $translation_delegate = new TranslationDelegate($this->repository);

    $translation_delegate->translate('test', $invalid_code, $invalid_code);
  }

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

    $translation_delegate = new TranslationDelegate($this->repository, $api);

    $actual_result = $translation_delegate->translateProject($project, 'en', 'fr');

    $this->assertEquals([$translation_result, null, null], $actual_result);
  }

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

    $translation_delegate = new TranslationDelegate($this->repository, $api);

    $actual_result = $translation_delegate->translateProject($project, 'en', 'fr');

    $this->assertEquals([$translation_result, $translation_result, $translation_result], $actual_result);
  }

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

    $translation_delegate = new TranslationDelegate($this->repository, $api);

    $actual_result = $translation_delegate->translateProject($project, 'en', 'fr');

    $this->assertNull($actual_result);
  }

  public function testAddProjectCustomTranslation(): void
  {
    $translation_delegate = new TranslationDelegate($this->repository);
    $project = new Program();

    $this->repository->expects($this->once())
      ->method('addNameTranslation')
      ->with($project, 'fr', 'test')
      ->willReturn(true)
    ;

    $this->repository->expects($this->once())
      ->method('addDescriptionTranslation')
      ->with($project, 'fr', 'test')
      ->willReturn(true)
    ;

    $this->repository->expects($this->once())
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

  public function provideInvalidLanguageCode(): array
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
}
