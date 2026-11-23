<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Translation;

use App\DB\Entity\Project\Project;
use App\DB\EntityRepository\Translation\ProjectCustomTranslationRepository;
use App\DB\EntityRepository\Translation\ProjectMachineTranslationRepository;
use App\Translation\LibreTranslateApi;
use App\Translation\ProjectTranslationJob;
use App\Translation\TranslateProject;
use App\Translation\TranslationDelegate;
use App\Translation\TranslationResult;
use App\Translation\UnsupportedLanguageException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

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
      ['EN-US'], // need to be lowercase en
      ['en-us'], // need to be uppercase US
      ['xx-US'],
    ];
  }

  public function testTranslateProjectCachesTheTranslationUnderTheDetectedLanguage(): void
  {
    $project = $this->project('name', null, 'credit');
    $repository = $this->createMock(ProjectMachineTranslationRepository::class);
    $repository->expects($this->once())->method('cacheTranslation')
      ->with($project, 'de', 'fr', true, 'translated name', null, 'translated credit')
    ;

    $this->delegate($this->translatingApi(), $repository)->translateProject($project, null, 'fr');
  }

  public function testTranslateProjectWithAPickedSourceIsCachedAsNotAutoDetected(): void
  {
    $project = $this->project('name');
    $repository = $this->createMock(ProjectMachineTranslationRepository::class);
    $repository->expects($this->once())->method('cacheTranslation')->with($project, 'ja', 'en', false, 'translated name', null, null);

    $this->delegate($this->translatingApi(), $repository)->translateProject($project, 'ja', 'en');
  }

  public function testTranslateProjectSkipsBlankTextsAndWaitsLongerThanAWebRequest(): void
  {
    $api = $this->createMock(LibreTranslateApi::class);
    $api->expects($this->once())->method('detectLanguage')->with('name')->willReturn(['language' => 'de', 'confidence' => 100.0]);
    $api->expects($this->once())->method('translate')
      ->with('name', 'de', 'fr', $this->greaterThan(5))
      ->willReturn($this->translationResult('translated name', 'de'))
    ;

    $this->delegate($api)->translateProject($this->project('name', '', '  '), null, 'fr');
  }

  public function testTranslateProjectDetectsLanguageFromCombinedTextWhenTitleIsMisleading(): void
  {
    $project = $this->project('Flappy', 'Ein tolles Spiel für alle', null);
    $api = $this->createMock(LibreTranslateApi::class);
    $api->expects($this->once())->method('detectLanguage')
      ->with("Flappy\nEin tolles Spiel für alle")
      ->willReturn(['language' => 'de', 'confidence' => 95.0])
    ;
    $api->expects($this->exactly(2))->method('translate')->willReturnCallback(
      fn (string $text, ?string $source, string $target): TranslationResult => match ([$text, $source, $target]) {
        ['Flappy', 'de', 'en'] => $this->translationResult('Flappy'),
        ['Ein tolles Spiel für alle', 'de', 'en'] => $this->translationResult('A great game for everyone'),
        default => throw new \UnexpectedValueException("Unexpected call: {$text}, {$source}, {$target}"),
      }
    );

    $repository = $this->createMock(ProjectMachineTranslationRepository::class);
    $repository->expects($this->once())->method('cacheTranslation')
      ->with($project, 'de', 'en', true, 'Flappy', 'A great game for everyone', null)
    ;

    $this->delegate($api, $repository)->translateProject($project, null, 'en');
  }

  public function testTranslateProjectDetectsLanguageFromCombinedTextWhenTitleIsShort(): void
  {
    $project = $this->project('123', 'Ein tolles Spiel', null);
    $api = $this->createMock(LibreTranslateApi::class);
    $api->expects($this->once())->method('detectLanguage')
      ->with("123\nEin tolles Spiel")
      ->willReturn(['language' => 'de', 'confidence' => 85.0])
    ;
    $api->expects($this->exactly(2))->method('translate')->willReturnCallback(
      fn (string $text, ?string $source, string $target): TranslationResult => match ([$text, $source, $target]) {
        ['123', 'de', 'en'] => $this->translationResult('123'),
        ['Ein tolles Spiel', 'de', 'en'] => $this->translationResult('A great game'),
        default => throw new \UnexpectedValueException('Unexpected text: '.$text),
      }
    );

    $repository = $this->createMock(ProjectMachineTranslationRepository::class);
    $repository->expects($this->once())->method('cacheTranslation')
      ->with($project, 'de', 'en', true, '123', 'A great game', null)
    ;

    $this->delegate($api, $repository)->translateProject($project, null, 'en');
  }

  public function testTranslateProjectDetectsLanguageFromCreditsWhenTitleAndDescriptionAreShort(): void
  {
    $project = $this->project('Game', 'v1', 'Gracias a todos');
    $api = $this->createMock(LibreTranslateApi::class);
    $api->expects($this->once())->method('detectLanguage')
      ->with("Game\nv1\nGracias a todos")
      ->willReturn(['language' => 'es', 'confidence' => 90.0])
    ;
    $api->expects($this->exactly(3))->method('translate')->willReturnCallback(
      fn (string $text, ?string $source, string $target): TranslationResult => match ([$text, $source, $target]) {
        ['Game', 'es', 'en'] => $this->translationResult('Game'),
        ['v1', 'es', 'en'] => $this->translationResult('v1'),
        ['Gracias a todos', 'es', 'en'] => $this->translationResult('Thanks everyone'),
        default => throw new \UnexpectedValueException('Unexpected text: '.$text),
      }
    );

    $repository = $this->createMock(ProjectMachineTranslationRepository::class);
    $repository->expects($this->once())->method('cacheTranslation')
      ->with($project, 'es', 'en', true, 'Game', 'v1', 'Thanks everyone')
    ;

    $this->delegate($api, $repository)->translateProject($project, null, 'en');
  }

  public function testTranslateProjectFailsWhenLanguageCannotBeDetected(): void
  {
    $project = $this->project('123', null, null);
    $api = $this->createMock(LibreTranslateApi::class);
    $api->expects($this->once())->method('detectLanguage')
      ->with('123')
      ->willReturn(null)
    ;
    $api->expects($this->never())->method('translate');

    $repository = $this->createMock(ProjectMachineTranslationRepository::class);
    $repository->expects($this->never())->method('cacheTranslation');

    $this->delegate($api, $repository)->translateProject($project, null, 'en');
  }

  public function testRequestQueuesOneJobUntilItIsDone(): void
  {
    $bus = $this->createMock(MessageBusInterface::class);
    $bus->expects($this->once())->method('dispatch')
      ->with(new TranslateProject('p1', null, 'fr'))
      ->willReturnCallback(static fn (object $message): Envelope => new Envelope($message))
    ;
    $delegate = $this->delegate(bus: $bus);
    $project = $this->project('name');

    $this->assertSame(ProjectTranslationJob::Queued, $delegate->requestProjectTranslation($project, null, 'fr', static fn (): bool => true));
    // A poll of the queued job neither queues another nor asks the rate limit.
    $asked = false;
    $this->assertSame(ProjectTranslationJob::Queued, $delegate->requestProjectTranslation($project, null, 'fr', static function () use (&$asked): bool {
      $asked = true;

      return true;
    }));
    $this->assertFalse($asked);
  }

  public function testARefusedRequestQueuesNothing(): void
  {
    $bus = $this->createMock(MessageBusInterface::class);
    $bus->expects($this->never())->method('dispatch');

    $this->assertSame(ProjectTranslationJob::Refused, $this->delegate(bus: $bus)->requestProjectTranslation($this->project('name'), null, 'fr', static fn (): bool => false));
  }

  public function testAJobThatThrowsIsReportedAsFailed(): void
  {
    $repository = $this->createStub(ProjectMachineTranslationRepository::class);
    $repository->method('cacheTranslation')->willThrowException(new \RuntimeException('db down'));
    $delegate = $this->delegate($this->translatingApi(), $repository);
    $project = $this->project('name');

    $delegate->translateProject($project, null, 'fr');

    $this->assertSame(ProjectTranslationJob::Failed, $delegate->requestProjectTranslation($project, null, 'fr', static fn (): bool => true));
  }

  public function testAFailedJobIsReportedOnceThenQueuedAgain(): void
  {
    $api = $this->createStub(LibreTranslateApi::class);
    $api->method('detectLanguage')->willReturn(['language' => 'de', 'confidence' => 100.0]);
    $api->method('translate')->willReturn(null);
    $repository = $this->createMock(ProjectMachineTranslationRepository::class);
    $repository->expects($this->never())->method('cacheTranslation');
    $bus = $this->createMock(MessageBusInterface::class);
    $bus->expects($this->once())->method('dispatch')->willReturnCallback(static fn (object $message): Envelope => new Envelope($message));
    $delegate = $this->delegate($api, $repository, $bus);
    $project = $this->project('name');

    $delegate->translateProject($project, null, 'fr');

    $this->assertSame(ProjectTranslationJob::Failed, $delegate->requestProjectTranslation($project, null, 'fr', static fn (): bool => true));
    $this->assertSame(ProjectTranslationJob::Queued, $delegate->requestProjectTranslation($project, null, 'fr', static fn (): bool => true));
  }

  public function testAJobWithoutALanguageModelIsReportedAsUnsupported(): void
  {
    $api = $this->createStub(LibreTranslateApi::class);
    $api->method('detectLanguage')->willReturn(['language' => 'de', 'confidence' => 100.0]);
    $api->method('translate')->willThrowException(new UnsupportedLanguageException('kk is not supported'));
    $logger = $this->createMock(LoggerInterface::class);
    $logger->expects($this->never())->method('error');
    $bus = $this->createStub(MessageBusInterface::class);
    $bus->method('dispatch')->willReturnCallback(static fn (object $message): Envelope => new Envelope($message));
    $delegate = new TranslationDelegate(
      $this->createStub(ProjectCustomTranslationRepository::class),
      $this->createStub(ProjectMachineTranslationRepository::class),
      $api,
      new ArrayAdapter(),
      $bus,
      $logger,
    );
    $project = $this->project('name');

    $delegate->translateProject($project, null, 'kk');

    $this->assertSame(ProjectTranslationJob::Unsupported, $delegate->requestProjectTranslation($project, null, 'kk', static fn (): bool => true));
    $this->assertSame(ProjectTranslationJob::Queued, $delegate->requestProjectTranslation($project, null, 'kk', static fn (): bool => true));
  }

  public function testCachedProjectTranslationComesFromTheRepository(): void
  {
    $cached_translation = [new TranslationResult(), null, null];
    $repository = $this->createMock(ProjectMachineTranslationRepository::class);
    $repository->expects($this->once())->method('getCachedTranslation')->with($this->anything(), 'sr', 'en')->willReturn($cached_translation);

    $this->assertSame($cached_translation, $this->delegate(repository: $repository)->getCachedProjectTranslation(new Project(), 'sr-SP', 'en'));
  }

  public function testAddProjectCustomTranslation(): void
  {
    $custom_translation_repository = $this->createMock(ProjectCustomTranslationRepository::class);
    $translation_delegate = new TranslationDelegate(
      $custom_translation_repository,
      $this->createStub(ProjectMachineTranslationRepository::class),
      $this->createStub(LibreTranslateApi::class),
      new ArrayAdapter(),
      $this->createStub(MessageBusInterface::class),
      new NullLogger(),
    );
    $project = new Project();

    $custom_translation_repository->expects($this->once())->method('addNameTranslation')->with($project, 'fr', 'test')->willReturn(true);
    $custom_translation_repository->expects($this->once())->method('addDescriptionTranslation')->with($project, 'fr', 'test')->willReturn(true);
    $custom_translation_repository->expects($this->once())->method('addCreditTranslation')->with($project, 'fr', 'test')->willReturn(true);

    $this->assertTrue($translation_delegate->addProjectNameCustomTranslation($project, 'fr', 'test'));
    $this->assertTrue($translation_delegate->addProjectDescriptionCustomTranslation($project, 'fr', 'test'));
    $this->assertTrue($translation_delegate->addProjectCreditCustomTranslation($project, 'fr', 'test'));
  }

  private function delegate(?LibreTranslateApi $api = null, ?ProjectMachineTranslationRepository $repository = null, ?MessageBusInterface $bus = null): TranslationDelegate
  {
    return new TranslationDelegate(
      $this->createStub(ProjectCustomTranslationRepository::class),
      $repository ?? $this->createStub(ProjectMachineTranslationRepository::class),
      $api ?? $this->createStub(LibreTranslateApi::class),
      new ArrayAdapter(),
      $bus ?? $this->createStub(MessageBusInterface::class),
      new NullLogger(),
    );
  }

  private function project(string $name, ?string $description = null, ?string $credits = null): Project
  {
    $project = new Project();
    $project->setId('p1');
    $project->setName($name);
    $project->setDescription($description);
    $project->setCredits($credits);

    return $project;
  }

  private function translationResult(string $translation, ?string $detected = null): TranslationResult
  {
    $result = new TranslationResult();
    $result->translation = $translation;
    $result->detected_source_language = $detected;

    return $result;
  }

  private function translatingApi(): LibreTranslateApi
  {
    $api = $this->createStub(LibreTranslateApi::class);
    $api->method('detectLanguage')->willReturn(['language' => 'de', 'confidence' => 100.0]);
    $api->method('translate')->willReturnCallback(fn (string $text, ?string $source): TranslationResult => $this->translationResult('translated '.$text, null === $source ? 'de' : null));

    return $api;
  }
}
