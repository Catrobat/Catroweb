<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Admin\System\Logs;

use App\Admin\System\Logs\LogsController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/** @internal */
#[CoversClass(LogsController::class)]
#[Group('unit')]
final class LogsControllerTest extends TestCase
{
  private string $root;

  private string $logDir;

  #[\Override]
  protected function setUp(): void
  {
    $this->root = sys_get_temp_dir().'/catroweb-logs-'.bin2hex(random_bytes(8));
    $this->logDir = $this->root.'/var/log/';
    mkdir($this->logDir.'prod', 0o700, true);
    file_put_contents($this->logDir.'prod/app.log', "log line\n");
    // A file outside the log directory sharing a log's basename: the old download check only
    // compared basenames, so `prod/../../app.log` reached this.
    file_put_contents($this->root.'/var/app.log', "secret\n");
    symlink($this->root.'/var/app.log', $this->logDir.'prod/link.log');
  }

  #[\Override]
  protected function tearDown(): void
  {
    new Filesystem()->remove($this->root);
  }

  public function testFileInsideTheLogDirectoryResolvesToItsRealPath(): void
  {
    self::assertSame(realpath($this->logDir.'prod/app.log'), LogsController::resolveLogFile('prod/app.log', $this->logDir));
  }

  #[DataProvider('provideOutsideNames')]
  public function testAnythingOutsideTheLogDirectoryIsRejected(string $file): void
  {
    self::assertNull(LogsController::resolveLogFile($file, $this->logDir));
  }

  public static function provideOutsideNames(): \Generator
  {
    yield 'traversal to a file sharing a log basename' => ['prod/../../app.log'];
    yield 'plain traversal' => ['../app.log'];
    yield 'symlink out of the log directory' => ['prod/link.log'];
    yield 'missing file' => ['prod/missing.log'];
    yield 'stream wrapper' => ['php://filter/resource=prod/app.log'];
    yield 'the log directory itself' => [''];
  }
}
