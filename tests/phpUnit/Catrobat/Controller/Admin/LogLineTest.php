<?php

namespace Tests\phpUnit\Catrobat\Controller\Admin;

use App\Admin\Tools\Logs\LogLine;
use App\Admin\Tools\Logs\LogsController;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @covers \App\Admin\Tools\Logs\LogLine
 */
class LogLineTest extends CatrowebTestCase
{
  private MockObject $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockForAbstractClass(LogLine::class);
  }

  protected function tearDown(): void
  {
    parent::tearDown();
  }

  public function getDebugLevelDataProvider(): \Generator
  {
    yield 'case 1' => ['php.INFO', LogsController::FILTER_LEVEL_INFO];
    yield 'case 2' => ['console.WARNING', LogsController::FILTER_LEVEL_WARNING];
    yield 'case 3' => ['console.ERROR', LogsController::FILTER_LEVEL_ERROR];
    yield 'case 4' => ['php.DEBUG', LogsController::FILTER_LEVEL_DEBUG];
    yield 'case 5' => ['request.CRITICAL', LogsController::FILTER_LEVEL_CRITICAL];
    yield 'case 6' => ['console.NOTICE', LogsController::FILTER_LEVEL_NOTICE];
    yield 'case 7' => ['console.ALERT', LogsController::FILTER_LEVEL_ALERT];
    yield 'case 8' => ['console.EMERGENCY', LogsController::FILTER_LEVEL_EMERGENCY];
    yield 'case 9' => ['Nothing', LogsController::FILTER_LEVEL_DEBUG];
  }

  /**
   * @dataProvider getDebugLevelDataProvider
   *
   * @throws ReflectionException
   */
  public function testGetDebugLevelByLine(string $string, int $output): void
  {
    $this->assertEquals($this->invokeMethod($this->object, 'getDebugLevelByString', [$string]), $output);
  }

  /**
   * @throws ReflectionException
   */
  public function testDate(): void
  {
    $date = '21.04.1993';
    $this->invokeMethod($this->object, 'setDate', [$date]);
    $this->assertEquals($this->invokeMethod($this->object, 'getDate'), $date);
  }

  /**
   * @throws ReflectionException
   */
  public function testDebugCode(): void
  {
    $debug_code = 'testCode';
    $this->invokeMethod($this->object, 'setDebugCode', [$debug_code]);
    $this->assertEquals($this->invokeMethod($this->object, 'getDebugCode'), $debug_code);
  }

  /**
   * @throws ReflectionException
   */
  public function testDebugLevel(): void
  {
    $debug_level = 0;
    $this->invokeMethod($this->object, 'setDebugLevel', [$debug_level]);
    $this->assertEquals($this->invokeMethod($this->object, 'getDebugLevel'), $debug_level);
  }

  /**
   * @throws ReflectionException
   */
  public function testMsg(): void
  {
    $msg = 'message';
    $this->invokeMethod($this->object, 'setMsg', [$msg]);
    $this->assertEquals($this->invokeMethod($this->object, 'getMsg'), $msg);
  }
}
