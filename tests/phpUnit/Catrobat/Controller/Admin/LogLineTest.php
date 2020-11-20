<?php

namespace Tests\phpUnit\Catrobat\Controller\Admin;

use App\Catrobat\Controller\Admin\LogLine;
use App\Catrobat\Controller\Admin\LogsController;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @covers \App\Catrobat\Controller\Admin\LogLine
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
   * @covers \LogLine::getDebugLevelByLine
   * @dataProvider getDebugLevelDataProvider
   *
   * @throws ReflectionException
   */
  public function testGetDebugLevelByLine(string $string, int $output): void
  {
    $this->assertEquals($this->invokeMethod($this->object, 'getDebugLevelByString', [$string]), $output);
  }

  /**
   * @covers \LogLine::setDate, LogLine::getDate
   */
  public function testDate(): void
  {
    $date = '21.04.1993';
    $this->object->setDate($date);
    $this->assertEquals($this->object->getDate(), $date);
  }

  /**
   * @covers \LogLine::setDebugCode, LogLine::getDebugCode
   */
  public function testDebugCode(): void
  {
    $debug_code = 'testCode';
    $this->object->setDebugCode($debug_code);
    $this->assertEquals($this->object->getDebugCode(), $debug_code);
  }

  /**
   * @covers \LogLine::setDebugLevel, LogLine::getDebugLevel
   */
  public function testDebugLevel(): void
  {
    $debug_level = 0;
    $this->object->setDebugLevel($debug_level);
    $this->assertEquals($this->object->getDebugLevel(), $debug_level);
  }

  /**
   * @covers \LogLine::setMsg, LogLine::getMsg
   */
  public function testMsg(): void
  {
    $msg = 'message';
    $this->object->setMsg($msg);
    $this->assertEquals($this->object->getMsg(), $msg);
  }
}
