<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Translation;

use App\DB\Entity\Project\Project;
use App\DB\EntityRepository\Translation\ProjectCustomTranslationRepository;
use App\DB\EntityRepository\Translation\ProjectMachineTranslationRepository;
use App\Translation\LibreTranslateApi;
use App\Translation\TranslationDelegate;
use App\Translation\TranslationResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TranslationDelegate::class)]
class TranslationDelegateTest extends TestCase
{
  public function testTranslateUsesTheApi(): void
  {
    $expected_result = new TranslationResult();
    $api = $this->createMock(LibreTranslateApi::class);
    $api->expects($this->once())->method('translate')->with('test', 'en', 'fr')->willReturn($expected_result);

    $this->assertSame($expected_result, $this->delegate($api)->translate('test', 'en', 'fr'));
  }

  #[DataProvider('provideInvalidLanguageCode')]
  public function testInvalidLanguageCode(string $invalid_code): void
  {
    $this->expectException(\InvalidArgumentException::class);

    $this->delegate()->translate('test', $invalid_code, $invalid_code);
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

  public function testTranslateProjectCachesTheFirstTranslationUnderTheDetectedLanguage(): void
  {
    $project = new Project();
    $project->setName('name');
    $project->setCredits('credit');

    $api = $this->createStub(LibreTranslateApi::class);
    $api->method('translate')->willReturnCallback(static function (string $text): TranslationResult {
      $result = new TranslationResult();
      $result->translation = 'translated '.$text;
      $result->detected_source_language = 'de';

      return $result;
    });
    $repository = $this->createMock(ProjectMachineTranslationRepository::class);
    $repository->method('getCachedTranslation')->willReturn(null);
    $repository->expects($this->once())->method('cacheTranslation')
      ->with($project, 'de', 'fr', 'translated name', null, 'translated credit')
    ;

    $result = $this->delegate($api, $repository)->translateProject($project, null, 'fr');

    $this->assertNotNull($result);
    $this->assertSame('translated name', $result[0]?->translation);
    $this->assertNull($result[1]);
    $this->assertSame('translated credit', $result[2]?->translation);
  }

  public function testTranslateProjectSkipsBlankTexts(): void
  {
    $project = new Project();
    $project->setName('name');
    $project->setDescription('');
    $project->setCredits('  ');

    $result = new TranslationResult();
    $result->translation = 'translated name';
    $result->detected_source_language = 'de';
    $api = $this->createMock(LibreTranslateApi::class);
    $api->expects($this->once())->method('translate')->with('name')->willReturn($result);

    $translation = $this->delegate($api)->translateProject($project, null, 'fr');

    $this->assertSame([$result, null, null], $translation);
  }

  public function testTranslateProjectWithAnExplicitSourceIsNotCached(): void
  {
    $project = new Project();
    $project->setName('name');

    $result = new TranslationResult();
    $result->translation = 'translated';
    $api = $this->createStub(LibreTranslateApi::class);
    $api->method('translate')->willReturn($result);
    $repository = $this->createMock(ProjectMachineTranslationRepository::class);
    $repository->method('getCachedTranslation')->willReturn(null);
    $repository->expects($this->never())->method('cacheTranslation');

    $this->assertNotNull($this->delegate($api, $repository)->translateProject($project, 'ja', 'en'));
  }

  public function testTranslateProjectFailureCachesNothing(): void
  {
    $project = new Project();
    $project->setName('name');

    $api = $this->createStub(LibreTranslateApi::class);
    $api->method('translate')->willReturn(null);
    $repository = $this->createMock(ProjectMachineTranslationRepository::class);
    $repository->method('getCachedTranslation')->willReturn(null);
    $repository->expects($this->never())->method('cacheTranslation');

    $this->assertNull($this->delegate($api, $repository)->translateProject($project, 'en', 'fr'));
  }

  public function testCachedProjectTranslationSkipsTheApi(): void
  {
    $cached_translation = [new TranslationResult(), null, null];
    $repository = $this->createStub(ProjectMachineTranslationRepository::class);
    $repository->method('getCachedTranslation')->willReturn($cached_translation);
    $api = $this->createMock(LibreTranslateApi::class);
    $api->expects($this->never())->method('translate');

    $this->assertSame($cached_translation, $this->delegate($api, $repository)->translateProject(new Project(), 'en', 'fr'));
  }

  public function testAddProjectCustomTranslation(): void
  {
    $custom_translation_repository = $this->createMock(ProjectCustomTranslationRepository::class);
    $translation_delegate = new TranslationDelegate(
      $custom_translation_repository,
      $this->createStub(ProjectMachineTranslationRepository::class),
      $this->createStub(LibreTranslateApi::class),
    );
    $project = new Project();

    $custom_translation_repository->expects($this->once())->method('addNameTranslation')->with($project, 'fr', 'test')->willReturn(true);
    $custom_translation_repository->expects($this->once())->method('addDescriptionTranslation')->with($project, 'fr', 'test')->willReturn(true);
    $custom_translation_repository->expects($this->once())->method('addCreditTranslation')->with($project, 'fr', 'test')->willReturn(true);

    $this->assertTrue($translation_delegate->addProjectNameCustomTranslation($project, 'fr', 'test'));
    $this->assertTrue($translation_delegate->addProjectDescriptionCustomTranslation($project, 'fr', 'test'));
    $this->assertTrue($translation_delegate->addProjectCreditCustomTranslation($project, 'fr', 'test'));
  }

  private function delegate(?LibreTranslateApi $api = null, ?ProjectMachineTranslationRepository $repository = null): TranslationDelegate
  {
    return new TranslationDelegate(
      $this->createStub(ProjectCustomTranslationRepository::class),
      $repository ?? $this->createStub(ProjectMachineTranslationRepository::class),
      $api ?? $this->createStub(LibreTranslateApi::class),
    );
  }
}
