<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CodeView;

use App\DB\Entity\Project\Project;
use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\CatrobatFile\ExtractedFileRepository;
use App\Project\CodeView\CodeExportRunner;
use App\Project\CodeView\CodeExportUnavailableException;
use App\Project\CodeView\CodeViewExportEventListener;
use App\Project\CodeView\CodeViewExportStore;
use App\Project\Event\ProjectAfterInsertEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
#[CoversClass(CodeViewExportStore::class)]
class CodeViewExportStoreTest extends TestCase
{
  private string $dir;

  private Project $project;

  #[\Override]
  protected function setUp(): void
  {
    $this->dir = sys_get_temp_dir().'/code-view-store-'.bin2hex(random_bytes(4)).'/';
    (new Filesystem())->mkdir([$this->dir.'project', $this->dir.'store']);
    $this->project = new Project();
    $this->project->setId('a1b2c3');
    $this->writeCode('<program><object name="Cat"/></program>');
  }

  #[\Override]
  protected function tearDown(): void
  {
    (new Filesystem())->remove($this->dir);
  }

  public function testExportsOnceAndKeepsIt(): void
  {
    $runner = $this->createMock(CodeExportRunner::class);
    $runner->method('getVersion')->willReturn('v1');
    $runner->expects($this->once())->method('export')->willReturn(['scenes' => []]);
    $store = $this->store($runner);

    $this->assertSame(['scenes' => []], $store->get($this->project));
    $this->assertSame(['scenes' => []], $store->get($this->project), 'kept');
    $this->assertFileExists($this->dir.'store/a1b2c3.json');
  }

  public function testExportsAgainWhenTheCodeOrTheExportChanges(): void
  {
    $version = 'v1';
    $runner = $this->createMock(CodeExportRunner::class);
    $runner->method('getVersion')->willReturnCallback(static function () use (&$version): string {
      return $version;
    });
    $runner->expects($this->exactly(3))->method('export')->willReturn(['scenes' => []]);
    $store = $this->store($runner);

    $store->get($this->project);
    $this->writeCode('<program><object name="Dog"/></program>');
    $store->get($this->project);
    $version = 'v2';
    $store->get($this->project);
    $store->refreshQueued();
    $store->get($this->project);
    $store->refreshQueued();
  }

  public function testAnOutdatedExportIsShownAtOnceAndMadeAgainAfterTheResponse(): void
  {
    $version = 'v1';
    $result = ['scenes' => ['old']];
    $runner = $this->createMock(CodeExportRunner::class);
    $runner->method('getVersion')->willReturnCallback(static function () use (&$version): string {
      return $version;
    });
    $runner->expects($this->exactly(2))->method('export')->willReturnCallback(static function () use (&$result): array {
      return $result;
    });
    $store = $this->store($runner);
    $store->get($this->project);

    $version = 'v2';
    $result = ['scenes' => ['new']];
    $this->assertSame(['scenes' => ['old']], $store->get($this->project), 'no waiting for the newer export');
    $this->assertSame(['scenes' => ['old']], $store->get($this->project), 'queued once');
    $store->refreshQueued();
    $this->assertSame(['scenes' => ['new']], $store->get($this->project));
  }

  public function testANewCodeXmlIsNotShownWithTheOldExport(): void
  {
    $runner = $this->createMock(CodeExportRunner::class);
    $runner->method('getVersion')->willReturn('v1');
    $runner->expects($this->exactly(2))->method('export')->willReturnCallback(fn (): array => ['scenes' => [file_get_contents($this->dir.'project/code.xml')]]);
    $store = $this->store($runner);
    $store->get($this->project);

    $this->writeCode('<program><object name="Dog"/></program>');

    $this->assertSame(['scenes' => ['<program><object name="Dog"/></program>']], $store->get($this->project));
  }

  public function testAViewDoesNotWaitWhenAllExportSlotsAreBusy(): void
  {
    $runner = $this->createMock(CodeExportRunner::class);
    $runner->method('getVersion')->willReturn('v1');
    $runner->expects($this->never())->method('export');
    $store = $this->store($runner);
    $slots = $this->holdLocks(array_map(static fn (int $i): string => '.slot-'.$i, range(0, CodeViewExportStore::SLOTS - 1)));

    $this->expectException(CodeExportUnavailableException::class);
    try {
      $store->get($this->project);
    } finally {
      $this->releaseLocks($slots);
    }
  }

  public function testOneFreeSlotIsEnough(): void
  {
    $runner = $this->createMock(CodeExportRunner::class);
    $runner->method('getVersion')->willReturn('v1');
    $runner->expects($this->once())->method('export')->willReturn(['scenes' => []]);
    $slots = $this->holdLocks(['.slot-0']);

    $this->assertSame(['scenes' => []], $this->store($runner)->get($this->project));
    $this->releaseLocks($slots);
  }

  public function testAViewDoesNotWaitForTheSameProjectsExport(): void
  {
    $runner = $this->createMock(CodeExportRunner::class);
    $runner->method('getVersion')->willReturn('v1');
    $runner->expects($this->never())->method('export');
    $locks = $this->holdLocks(['a1b2c3']);

    $this->expectException(CodeExportUnavailableException::class);
    try {
      $this->store($runner)->get($this->project);
    } finally {
      $this->releaseLocks($locks);
    }
  }

  public function testAfterTheResponseBusySlotsLeaveTheOutdatedExportForLater(): void
  {
    $version = 'v1';
    $runner = $this->createMock(CodeExportRunner::class);
    $runner->method('getVersion')->willReturnCallback(static function () use (&$version): string {
      return $version;
    });
    $runner->expects($this->exactly(2))->method('export')->willReturn(['scenes' => []]);
    $store = $this->store($runner);
    $store->get($this->project);
    $version = 'v2';

    $store->get($this->project);
    $slots = $this->holdLocks(array_map(static fn (int $i): string => '.slot-'.$i, range(0, CodeViewExportStore::SLOTS - 1)));
    $store->refreshQueued(); // no exception, nothing exported
    $this->releaseLocks($slots);

    $store->get($this->project);
    $store->refreshQueued();
  }

  public function testAnUploadedProjectIsExportedAfterTheResponse(): void
  {
    $runner = $this->createMock(CodeExportRunner::class);
    $runner->method('getVersion')->willReturn('v1');
    $runner->expects($this->once())->method('export')->willReturn(['scenes' => []]);
    $store = $this->store($runner);
    $event = $this->createStub(ProjectAfterInsertEvent::class);
    $event->method('getProjectEntity')->willReturn($this->project);

    (new CodeViewExportEventListener($store))->onProjectAfterInsert($event);
    $this->assertFileDoesNotExist($this->dir.'store/a1b2c3.json', 'not during the upload');
    $store->refreshQueued();
    $this->assertFileExists($this->dir.'store/a1b2c3.json');
  }

  public function testTheBackfillWaitsForASlot(): void
  {
    $runner = $this->createMock(CodeExportRunner::class);
    $runner->method('getVersion')->willReturn('v1');
    $runner->expects($this->once())->method('export')->willReturn(['scenes' => []]);

    $this->assertSame(['scenes' => []], $this->store($runner)->refresh($this->project, true));
  }

  public function testKeepsAFailedExportAndTheOlderOneWhenANewerExportFails(): void
  {
    $version = 'v1';
    $result = ['scenes' => ['old']];
    $runner = $this->createMock(CodeExportRunner::class);
    $runner->method('getVersion')->willReturnCallback(static function () use (&$version): string {
      return $version;
    });
    $runner->expects($this->exactly(2))->method('export')->willReturnCallback(static function () use (&$result): array {
      return $result;
    });
    $store = $this->store($runner);

    $this->assertSame(['scenes' => ['old']], $store->get($this->project));
    $version = 'v2';
    $result = ['error' => 'broken'];
    $store->get($this->project);
    $store->refreshQueued();
    $this->assertSame(['scenes' => ['old']], $store->get($this->project), 'the older export, as the newer one failed');
  }

  public function testAFailedExportIsNotMadeAgainOnEveryView(): void
  {
    $runner = $this->createMock(CodeExportRunner::class);
    $runner->method('getVersion')->willReturn('v1');
    $runner->expects($this->once())->method('export')->willReturn(['error' => 'broken']);
    $store = $this->store($runner);

    $this->assertSame(['error' => 'broken'], $store->get($this->project));
    $this->assertSame(['error' => 'broken'], $store->get($this->project));
  }

  public function testAnExportThatCouldNotRunIsNotKept(): void
  {
    $runner = $this->createMock(CodeExportRunner::class);
    $runner->method('getVersion')->willReturn('v1');
    $runner->expects($this->exactly(2))->method('export')->willReturnOnConsecutiveCalls(
      $this->throwException(new CodeExportUnavailableException('The code export did not finish')),
      ['scenes' => []],
    );
    $store = $this->store($runner);

    try {
      $store->get($this->project);
      $this->fail('a view gets no answer, not a lasting error');
    } catch (CodeExportUnavailableException) {
    }
    $this->assertFileDoesNotExist($this->dir.'store/a1b2c3.json');
    $this->assertSame(['scenes' => []], $store->get($this->project), 'tried again');
  }

  public function testAnOutdatedExportStaysWhenTheNewerCannotRun(): void
  {
    $version = 'v1';
    $runner = $this->createMock(CodeExportRunner::class);
    $runner->method('getVersion')->willReturnCallback(static function () use (&$version): string {
      return $version;
    });
    $runner->expects($this->exactly(2))->method('export')->willReturnOnConsecutiveCalls(
      ['scenes' => ['old']],
      $this->throwException(new CodeExportUnavailableException('The code export failed')),
    );
    $store = $this->store($runner);
    $store->get($this->project);
    $version = 'v2';

    $store->get($this->project);
    $store->refreshQueued();

    $this->assertSame(['scenes' => ['old']], $store->get($this->project));
  }

  public function testWithoutADeployedExport(): void
  {
    $runner = $this->createMock(CodeExportRunner::class);
    $runner->method('getVersion')->willReturn(null);
    $runner->expects($this->never())->method('export');

    $this->expectException(CodeExportUnavailableException::class);
    $this->store($runner)->get($this->project);
  }

  public function testWithoutProjectFiles(): void
  {
    $repository = $this->createStub(ExtractedFileRepository::class);
    $repository->method('loadProjectExtractedFile')->willReturn(null);
    $store = new CodeViewExportStore($this->createStub(CodeExportRunner::class), $repository, new NullLogger(), $this->dir.'store');

    $this->assertNull($store->get($this->project));
  }

  private function store(CodeExportRunner $runner): CodeViewExportStore
  {
    $repository = $this->createStub(ExtractedFileRepository::class);
    $repository->method('loadProjectExtractedFile')->willReturnCallback(fn (): ExtractedCatrobatFile => new ExtractedCatrobatFile($this->dir.'project/', '', 'a1b2c3'));

    return new CodeViewExportStore($runner, $repository, new NullLogger(), $this->dir.'store');
  }

  /**
   * @param list<string> $names
   *
   * @return list<resource>
   */
  private function holdLocks(array $names): array
  {
    (new Filesystem())->mkdir($this->dir.'store');

    return array_map(function (string $name) {
      $lock = fopen($this->dir.'store/'.$name.'.lock', 'c');
      $this->assertNotFalse($lock);
      $this->assertTrue(flock($lock, LOCK_EX | LOCK_NB));

      return $lock;
    }, $names);
  }

  /**
   * @param list<resource> $locks
   */
  private function releaseLocks(array $locks): void
  {
    foreach ($locks as $lock) {
      flock($lock, LOCK_UN);
      fclose($lock);
    }
  }

  private function writeCode(string $xml): void
  {
    file_put_contents($this->dir.'project/code.xml', $xml);
  }
}
