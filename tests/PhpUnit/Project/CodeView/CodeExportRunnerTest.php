<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CodeView;

use App\Project\CodeView\CodeExportRunner;
use App\Project\CodeView\CodeExportUnavailableException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Process\ExecutableFinder;

/**
 * Runs the test stand-in of the player's code export (tests/TestData/CodeExport) with Node, as on the server.
 *
 * @internal
 */
#[CoversClass(CodeExportRunner::class)]
class CodeExportRunnerTest extends TestCase
{
  private const string EXPORT = 'tests/TestData/CodeExport';

  private const string PROJECT = 'tests/TestData/DataFixtures/CodeView/SimpleProject/code.xml';

  #[\Override]
  protected function setUp(): void
  {
    if (null === (new ExecutableFinder())->find('node', null, ['/usr/local/bin', '/usr/bin'])) {
      $this->markTestSkipped('Node is not installed');
    }
  }

  public function testExportsTheProjectsCode(): void
  {
    $export = (new CodeExportRunner(self::EXPORT, new NullLogger()))->export(self::PROJECT);

    $this->assertArrayNotHasKey('error', $export);
    $this->assertSame('Background', $export['scenes'][0]['objects'][0]['name']);
    $this->assertSame('WaitBrick', $export['scenes'][0]['objects'][0]['scripts'][0]['bricks'][0]['type']);
  }

  public function testVersionOfTheExport(): void
  {
    $this->assertSame('test-export', (new CodeExportRunner(self::EXPORT, new NullLogger()))->getVersion());
    $this->assertNull((new CodeExportRunner('/nonexistent', new NullLogger()))->getVersion());
  }

  public function testAProjectTheExportCannotShowIsAnError(): void
  {
    $this->assertSame('code.xml is not valid XML', $this->exportOf('<program>BROKEN</program>')['error']);
  }

  public function testAnExportThatCrashesIsNoAnswer(): void
  {
    $this->expectException(CodeExportUnavailableException::class);
    $this->exportOf('<program>CRASH</program>');
  }

  public function testTheExportCannotReadOtherFiles(): void
  {
    $logger = new class extends AbstractLogger {
      public string $stderr = '';

      #[\Override]
      public function log($level, \Stringable|string $message, array $context = []): void
      {
        $this->stderr .= is_string($context['stderr'] ?? null) ? $context['stderr'] : '';
      }
    };

    try {
      $this->exportOf('<program>READ:'.(string) realpath('composer.json').'</program>', $logger);
      $this->fail('Node read a file it may not');
    } catch (CodeExportUnavailableException) {
    }
    $this->assertStringContainsString('ERR_ACCESS_DENIED', $logger->stderr);
  }

  public function testTheExportHasNoneOfSharesEnvironment(): void
  {
    $_ENV['CODE_EXPORT_TEST_SECRET'] = $_SERVER['CODE_EXPORT_TEST_SECRET'] = 'secret';
    putenv('CODE_EXPORT_TEST_SECRET=secret');
    try {
      $this->assertSame(['env' => null], $this->exportOf('<program>ENV:CODE_EXPORT_TEST_SECRET</program>'));
      $this->assertSame(['env' => null], $this->exportOf('<program>ENV:APP_ENV</program>'));
    } finally {
      unset($_ENV['CODE_EXPORT_TEST_SECRET'], $_SERVER['CODE_EXPORT_TEST_SECRET']);
      putenv('CODE_EXPORT_TEST_SECRET');
    }
  }

  public function testWithoutTheExport(): void
  {
    $this->expectException(CodeExportUnavailableException::class);
    (new CodeExportRunner('/nonexistent', new NullLogger()))->export(self::PROJECT);
  }

  public function testWithoutTheFile(): void
  {
    $this->expectException(CodeExportUnavailableException::class);
    (new CodeExportRunner(self::EXPORT, new NullLogger()))->export('/nonexistent/code.xml');
  }

  /**
   * @return array<string, mixed>
   */
  private function exportOf(string $code, LoggerInterface $logger = new NullLogger()): array
  {
    $xml = (string) tempnam(sys_get_temp_dir(), 'code');
    file_put_contents($xml, $code);
    try {
      return (new CodeExportRunner(self::EXPORT, $logger))->export($xml);
    } finally {
      unlink($xml);
    }
  }
}
