<?php

namespace Tests\Catrobat\Controller\Admin;

use App\Admin\Tools\Logs\LogLine;
use App\Admin\Tools\Logs\LogsController;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;
use Symfony\Component\Filesystem\Filesystem;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @covers \App\Admin\Tools\Logs\LogsController
 */
class LogsControllerTest extends CatrowebTestCase
{
  private MockObject $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockForAbstractClass(LogsController::class);
  }

  protected function tearDown(): void
  {
    parent::tearDown();
  }

  public function getLogFileContentDataProvider(): \Generator
  {
    yield 'case 1' => [
      ['[2020-10-20T19:20:55.523679+02:00] php.INFO: User Deprecated'],
      [0, true, 20],
      ['[2020-10-20T19:20:55.523679+02:00] php.INFO: User Deprecated'],
    ];

    yield 'case 2' => [
      [
        "[2020-11-05T06:56:56.863408+01:00] php.INFO: User Deprecated:\n",
        "[2020-11-05T06:56:56.864832+01:00] request.ERROR: Uncaught PHP Exception\n",
        "[2020-11-05T06:56:56.929542+01:00] php.INFO: User Deprecated:\n",
      ],
      [4, true, 20],
      ["[2020-11-05T06:56:56.864832+01:00] request.ERROR: Uncaught PHP Exception\n"],
    ];

    yield 'case 3' => [
      [
        "[2020-10-20T19:23:21.582916+02:00] php.INFO: User Deprecated:\n",
        "[2020-10-20T19:23:21.284864+02:00] console.ERROR: Error thrown while running command\n",
        "[2020-10-20T19:30:01.531211+02:00] request.CRITICAL: Uncaught PHP Exception\n",
      ],
      [4, false, 20],
      ["[2020-10-20T19:23:21.284864+02:00] console.ERROR: Error thrown while running command\n"],
    ];

    yield 'case 4' => [
      [
        "[2020-10-20T19:23:21.582916+02:00] php.INFO: User Deprecated:\n",
        "[2020-10-20T19:23:21.284864+02:00] console.ERROR: Error thrown while running command\n",
        "[2020-10-20T19:30:01.531211+02:00] request.CRITICAL: Uncaught PHP Exception\n",
      ],
      [4, true, 20],
      ["[2020-10-20T19:30:01.531211+02:00] request.CRITICAL: Uncaught PHP Exception\n",
        "[2020-10-20T19:23:21.284864+02:00] console.ERROR: Error thrown while running command\n", ],
    ];

    yield 'case 5' => [
      [
        "[2020-10-20T19:36:53.703303+02:00] request.INFO: Matched route\n",
        "[2020-10-20T22:44:12.937973+02:00] request.EMERGENCY: Uncaught PHP Exception\n",
        "[2020-10-20T19:36:53.894907+02:00] php.INFO: User Deprecated: Checking for the AdvancedUserInterface\n",
        "[2020-10-20T19:38:16.941917+02:00] console.ERROR: Error thrown\n",
        "[2020-10-20T22:44:12.937973+02:00] request.CRITICAL: Uncaught PHP Exception\n",
        "[2020-10-20T22:44:12.937973+02:00] request.ALERT: Uncaught PHP Exception\n",
      ],
      [5, true, 20],
      [
        "[2020-10-20T22:44:12.937973+02:00] request.ALERT: Uncaught PHP Exception\n",
        "[2020-10-20T22:44:12.937973+02:00] request.CRITICAL: Uncaught PHP Exception\n",
        "[2020-10-20T22:44:12.937973+02:00] request.EMERGENCY: Uncaught PHP Exception\n", ],
    ];
  }

  /**
   * @throws ReflectionException
   * @dataProvider getLogFileContentDataProvider
   */
  public function testGetLogFileContent(array $actualFileLines, array $searchFilters, array $expectedLines): void
  {
    $logDir = 'var/log/LogFilesTest/';
    $logFile = 'test.log';
    $fs = new Filesystem();

    if (!$fs->exists($logDir)) {
      $fs->mkdir($logDir, 0775);
      $fs->touch($logDir.$logFile);
      foreach ($actualFileLines as $fileLine) {
        $fs->appendToFile($logDir.$logFile, $fileLine);
      }
    }

    $searchParam = [];
    $searchParam['filter'] = $searchFilters[0];
    $searchParam['greater_equal_than_level'] = $searchFilters[1];
    $searchParam['line_count'] = $searchFilters[2];
    $rs = $this->invokeMethod($this->object, 'getLogFileContent', [$logFile, $logDir, $searchParam]);

    $expectedLinesArray = [];
    foreach ($expectedLines as $expectedLine) {
      $expectedLinesArray[] = new LogLine($expectedLine);
    }
    $i = 0;
    foreach ($rs as $line) {
      $this->assertEquals($line, $expectedLinesArray[$i]);
      ++$i;
    }

    $fs->remove($logDir);
  }

  /**
   * @throws ReflectionException
   */
  public function testGetAllLogFiles(): void
  {
    $logDir = 'var/log/test/';
    $logFilesList = ['test-phpunit-1.log', 'test-phpunit-2.log'];
    $fs = new Filesystem();
    if (!$fs->exists($logDir)) {
      $fs->mkdir($logDir, 0775);
    }

    foreach ($logFilesList as $file) {
      $fs->touch($logDir.$file);
    }

    $allFiles = $this->invokeMethod($this->object, 'getAllFilesInDirByPattern', ['var/log/', '*.log']);
    foreach ($logFilesList as $file) {
      $this->assertContains('test/'.$file, $allFiles);
    }
    $fs->remove($logDir);
  }
}
