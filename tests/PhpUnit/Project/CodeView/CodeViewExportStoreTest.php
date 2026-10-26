<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CodeView;

use App\DB\Entity\Project\Project;
use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\CatrobatFile\ExtractedFileRepository;
use App\Project\CodeView\CodeExportRunner;
use App\Project\CodeView\CodeViewExportStore;
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
    $store->get($this->project);
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

  public function testWithoutADeployedExport(): void
  {
    $runner = $this->createMock(CodeExportRunner::class);
    $runner->method('getVersion')->willReturn(null);
    $runner->expects($this->never())->method('export');

    $this->assertSame(['error' => 'The code export is not available'], $this->store($runner)->get($this->project));
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

  private function writeCode(string $xml): void
  {
    file_put_contents($this->dir.'project/code.xml', $xml);
  }
}
