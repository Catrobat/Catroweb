<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CodeView;

use App\DB\Entity\Project\Project;
use App\Project\CodeView\CodeViewExportStore;
use App\Project\CodeView\CodeViewTranslation;
use App\Project\CodeView\TranslateCodeView;
use App\Project\ProjectManager;
use App\Translation\LibreTranslateApi;
use App\Translation\UnsupportedLanguageException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\InMemoryStore;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;
use Tests\PhpUnit\System\BackgroundJob\RecordingMessageBus;

/** @internal */
#[CoversClass(CodeViewTranslation::class)]
final class CodeViewTranslationTest extends TestCase
{
  private RecordingMessageBus $bus;
  private LibreTranslateApi&MockObject $provider;
  private CodeViewTranslation $translation;
  /** @var array{code: string, export: string|null, data: array<string, mixed>} */
  private array $snapshot;

  #[\Override]
  protected function setUp(): void
  {
    $this->bus = new RecordingMessageBus();
    $this->provider = $this->createMock(LibreTranslateApi::class);
    $projects = $this->createStub(ProjectManager::class);
    $projects->method('find')->willReturn(new Project());
    $store = $this->createStub(CodeViewExportStore::class);
    $store->method('storedSnapshot')->willReturnCallback(fn (): array => $this->snapshot);
    $limiter = new RateLimiterFactory(['id' => 'code_view_translation', 'policy' => 'no_limit'], new InMemoryStorage());
    $this->translation = new CodeViewTranslation(new ArrayAdapter(), new LockFactory(new InMemoryStore()), $this->bus, $projects, $store, $this->provider, $limiter, new NullLogger());
  }

  public function testDetectionAndTextCacheReuseAcrossCodeChanges(): void
  {
    $this->setTexts('v1', ['Hello there, friend!', 'A second sentence']);
    $this->provider->expects($this->exactly(2))->method('detectLanguage')->willReturn(['language' => 'en', 'confidence' => 100.0]);
    $batches = [];
    $this->provider->expects($this->exactly(2))->method('translateBatch')->willReturnCallback(static function (array $texts, string $source) use (&$batches): array {
      self::assertSame('en', $source);
      $batches[] = $texts;

      return array_map(static fn (string $text): string => 'translated '.$text, $texts);
    });

    $this->assertSame('queued', $this->request()['status']);
    $this->assertSame('queued', $this->request()['status']);
    $this->assertCount(1, $this->bus->dispatched);
    $this->runJob();
    $this->assertCount(2, $this->request(poll: true)['translations']);

    $this->setTexts('v2', ['Hello there, friend!', 'A new sentence']);
    $this->request();
    $this->runJob(1);
    $this->assertCount(2, $this->request(poll: true)['translations']);
    $this->assertSame([['Hello there, friend!', 'A second sentence'], ['A new sentence']], $batches);
  }

  public function testPollingDoesNotResumePartialResult(): void
  {
    $this->setTexts('v1', array_map(static fn (int $i): string => 'Text '.$i, range(1, 13)));
    $this->provider->expects($this->once())->method('detectLanguage')->willReturn(['language' => 'en', 'confidence' => 100.0]);
    $this->provider->expects($this->exactly(3))->method('translateBatch')
      ->willReturnCallback(static fn (array $texts): array => array_map(static fn (string $text): string => 'translated '.$text, $texts))
    ;
    $this->request();
    $this->runJob();
    $partial = $this->request(poll: true);
    $this->assertSame('partial', $partial['status']);
    $this->assertTrue($partial['can_resume']);
    $this->assertCount(12, $partial['translations']);

    $this->assertSame($partial, $this->request());
    $this->assertCount(1, $this->bus->dispatched);
    $this->request(resume: true);
    $this->runJob(1);
    $complete = $this->request(poll: true);
    $this->assertSame('complete', $complete['status']);
    $this->assertCount(13, $complete['translations']);
  }

  public function testUnclearSourceAndUnsupportedLanguage(): void
  {
    $this->setTexts('v1', ['Hi']);
    $this->provider->expects($this->never())->method('detectLanguage');
    $this->provider->expects($this->once())->method('translateBatch')->willThrowException(new UnsupportedLanguageException());
    $this->request();
    $this->runJob();
    $this->assertSame('source_required', $this->request(poll: true)['status']);
    $this->request(source: 'ru');
    $this->runJob(1);
    $this->assertSame('unsupported', $this->request(poll: true, source: 'ru')['status']);
  }

  public function testSameLanguageFinishesWithoutBatching(): void
  {
    $this->setTexts('v1', array_map(static fn (int $i): string => 'Text '.$i, range(1, 13)));
    $this->provider->expects($this->never())->method('translateBatch');
    $this->request(source: 'de-DE');
    $this->runJob();

    $result = $this->request(poll: true, source: 'de-DE');
    $this->assertSame('complete', $result['status']);
    $this->assertSame([], $result['translations']);
    $this->assertFalse($result['can_resume']);
  }

  public function testLongNotesResumeAcrossJobs(): void
  {
    $note = str_repeat('français ', 125).str_repeat('espagnol ', 125);
    $this->setTexts('v1', [$note]);
    $this->provider->expects($this->atLeastOnce())->method('translateBatch')
      ->willReturnCallback(static function (array $texts): array {
        self::assertLessThanOrEqual(900, array_sum(array_map(mb_strlen(...), $texts)));

        return array_map(mb_strtoupper(...), $texts);
      })
    ;
    $this->request(source: 'fr');
    $this->runJob();
    $partial = $this->request(poll: true, source: 'fr');
    $this->assertSame('partial', $partial['status']);
    $this->assertSame([], $partial['translations']);

    for ($i = 1; $i <= 3; ++$i) {
      $this->request(resume: true, source: 'fr');
      $this->runJob($i);
      $result = $this->request(poll: true, source: 'fr');
      if ('complete' === $result['status']) {
        $this->assertSame(mb_strtoupper($note), $result['translations'][0]['text']);

        return;
      }
    }
    $this->fail('The long note did not finish');
  }

  private function setTexts(string $version, array $texts): void
  {
    $this->snapshot = [
      'code' => 'hash',
      'export' => $version,
      'data' => ['translation_parts' => true, 'segments' => array_map(
        static fn (string $text): array => ['kind' => 'string', 'source' => $text, 'text' => $text],
        $texts
      )],
    ];
  }

  private function request(bool $poll = false, bool $resume = false, ?string $source = null): array
  {
    $key = CodeViewExportStore::translationKey($this->snapshot);
    self::assertNotNull($key);

    return $this->translation->request('project', $key, $source, 'de', $poll, $resume, static fn (): bool => true);
  }

  private function runJob(int $index = 0): void
  {
    $job = $this->bus->dispatched[$index];
    $this->assertInstanceOf(TranslateCodeView::class, $job);
    $this->translation->translate($job);
  }
}
