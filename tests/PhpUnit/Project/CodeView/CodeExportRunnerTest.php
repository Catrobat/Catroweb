<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CodeView;

use App\Project\CodeView\CodeExportRunner;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
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

  public function testAnExportThatFailsIsAnError(): void
  {
    $this->assertSame('code.xml is not valid XML', $this->exportOf('<program>BROKEN</program>')['error']);
  }

  public function testTheExportCannotReadOtherFiles(): void
  {
    $other_file = (string) realpath('composer.json');

    $this->assertSame(['error' => 'The code export failed'], $this->exportOf('<program>READ:'.$other_file.'</program>'));
  }

  public function testWithoutTheExportOrTheFile(): void
  {
    $this->assertArrayHasKey('error', (new CodeExportRunner('/nonexistent', new NullLogger()))->export(self::PROJECT));
    $this->assertArrayHasKey('error', (new CodeExportRunner(self::EXPORT, new NullLogger()))->export('/nonexistent/code.xml'));
  }

  /**
   * @return array<string, mixed>
   */
  private function exportOf(string $code): array
  {
    $xml = (string) tempnam(sys_get_temp_dir(), 'code');
    file_put_contents($xml, $code);
    try {
      return (new CodeExportRunner(self::EXPORT, new NullLogger()))->export($xml);
    } finally {
      unlink($xml);
    }
  }
}
