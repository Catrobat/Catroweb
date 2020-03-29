<?php

namespace Tests\phpUnit\Catrobat\Services\Formatter;

use App\Catrobat\Services\Formatter\ElapsedTimeStringFormatter;
use App\Utils\TimeUtils;
use DateTime;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @internal
 * @coversNothing
 */
class ElapsedTimeStringFormatterTest extends TestCase
{
  protected int $test_time;

  private ElapsedTimeStringFormatter $elapsed_time_string_formatter;

  /**
   * @var MockObject|TranslatorInterface
   */
  private $translator;

  private array $translation_map;

  /**
   * @throws Exception
   */
  protected function setUp(): void
  {
    TimeUtils::freezeTime(new DateTime('2015-10-26 13:33:37'));
    $this->test_time = TimeUtils::getTimestamp();

    $this->translator = $this->createMock(TranslatorInterface::class);
    $this->elapsed_time_string_formatter = new ElapsedTimeStringFormatter($this->translator);

    $this->translation_map = [
      ['time.minutes.ago', ['%count%' => 0], 'catroweb', null, '< 1 minute ago'],
      ['time.minutes.ago', ['%count%' => 1], 'catroweb', null, '1 minute ago'],
      ['time.minutes.ago', ['%count%' => 5], 'catroweb', null, '5 minutes ago'],
      ['time.minutes.ago', ['%count%' => 59], 'catroweb', null, '59 minutes ago'],
      ['time.hours.ago', ['%count%' => 1], 'catroweb', null, '1 hour ago'],
      ['time.hours.ago', ['%count%' => 5], 'catroweb', null, '5 hours ago'],
      ['time.hours.ago', ['%count%' => 23], 'catroweb', null, '23 hours ago'],
      ['time.days.ago', ['%count%' => 1], 'catroweb', null, '1 day ago'],
      ['time.days.ago', ['%count%' => 5], 'catroweb', null, '5 days ago'],
      ['time.days.ago', ['%count%' => 6], 'catroweb', null, '6 days ago'],
      ['time.months.ago', ['%count%' => 1], 'catroweb', null, '1 month ago'],
      ['time.months.ago', ['%count%' => 6], 'catroweb', null, '6 months ago'],
      ['time.months.ago', ['%count%' => 11], 'catroweb', null, '11 months ago'],
      ['time.years.ago', ['%count%' => 1], 'catroweb', null, '1 year ago'],
      ['time.years.ago', ['%count%' => 3], 'catroweb', null, '3 years ago'],
      ['time.years.ago', ['%count%' => 100], 'catroweb', null, '100 years ago'],
    ];
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(ElapsedTimeStringFormatter::class, $this->elapsed_time_string_formatter);
  }

  /**
   * @throws Exception
   */
  public function testReturnsTheElapsedTimeSinceTimestampsInMinutes(): void
  {
    $this->translator
      ->expects($this->atLeast(7))
      ->method('trans')
      ->willReturn($this->returnValueMap($this->translation_map))
    ;

    $this->assertSame('< 1 minute ago', $this->elapsed_time_string_formatter->getElapsedTime($this->test_time + 10));
    $this->assertSame('< 1 minute ago', $this->elapsed_time_string_formatter->getElapsedTime($this->test_time));
    $this->assertSame('< 1 minute ago', $this->elapsed_time_string_formatter->getElapsedTime($this->test_time - 10));
    $this->assertSame('1 minute ago', $this->elapsed_time_string_formatter->getElapsedTime($this->test_time - 80));
    $this->assertSame('5 minutes ago', $this->elapsed_time_string_formatter->getElapsedTime($this->test_time - 60 * 5));
    $this->assertSame('5 minutes ago', $this->elapsed_time_string_formatter->getElapsedTime($this->test_time - 60 * 5 - 10));
    $this->assertSame('59 minutes ago', $this->elapsed_time_string_formatter->getElapsedTime($this->test_time - 60 * 59));
  }

  /**
   * @throws Exception
   */
  public function testReturnsTheElapsedTimeSinceTimestampsInHours(): void
  {
    $this->translator
      ->expects($this->atLeast(4))
      ->method('trans')
      ->willReturn($this->returnValueMap($this->translation_map))
    ;

    $this->assertSame('1 hour ago', $this->elapsed_time_string_formatter->getElapsedTime($this->test_time - 3_600));
    $this->assertSame('5 hours ago', $this->elapsed_time_string_formatter->getElapsedTime($this->test_time - 3_600 * 5));
    $this->assertSame('5 hours ago', $this->elapsed_time_string_formatter->getElapsedTime($this->test_time - 3_600 * 5 - 10));
    $this->assertSame('23 hours ago', $this->elapsed_time_string_formatter->getElapsedTime($this->test_time - 3_600 * 23));
  }

  /**
   * @throws Exception
   */
  public function testReturnsTheElapsedTimeSinceTimestampsInDays(): void
  {
    $this->translator
      ->expects($this->atLeast(4))
      ->method('trans')
      ->willReturn($this->returnValueMap($this->translation_map))
    ;

    $this->assertSame('1 day ago', $this->elapsed_time_string_formatter->getElapsedTime($this->test_time - 86_400));
    $this->assertSame('5 days ago', $this->elapsed_time_string_formatter->getElapsedTime($this->test_time - 86_400 * 5));
    $this->assertSame('5 days ago', $this->elapsed_time_string_formatter->getElapsedTime($this->test_time - 86_400 * 5 - 10));
    $this->assertSame('6 days ago', $this->elapsed_time_string_formatter->getElapsedTime($this->test_time - 86_400 * 6));
  }

  /**
   * @throws Exception
   */
  public function testReturnsTheElapsedTimeSinceTimestampsInMonths(): void
  {
    $this->translator
      ->expects($this->atLeast(4))
      ->method('trans')
      ->willReturn($this->returnValueMap($this->translation_map))
    ;

    $this->assertSame('1 month ago', $this->elapsed_time_string_formatter->getElapsedTime($this->test_time - 2_629_800));
    $this->assertSame('6 months ago', $this->elapsed_time_string_formatter->getElapsedTime($this->test_time - 2_629_800 * 6));
    $this->assertSame('6 months ago', $this->elapsed_time_string_formatter->getElapsedTime($this->test_time - 2_629_800 * 6 - 10));
    $this->assertSame('11 months ago', $this->elapsed_time_string_formatter->getElapsedTime($this->test_time - 2_629_800 * 11));
  }

  /**
   * @throws Exception
   */
  public function testReturnsTheElapsedTimeSinceTimestampsInYears(): void
  {
    $this->translator
      ->expects($this->atLeast(4))
      ->method('trans')
      ->willReturn($this->returnValueMap($this->translation_map))
    ;

    $this->assertSame('1 year ago', $this->elapsed_time_string_formatter->getElapsedTime($this->test_time - 31_557_600));
    $this->assertSame('3 years ago', $this->elapsed_time_string_formatter->getElapsedTime($this->test_time - 31_557_600 * 3));
    $this->assertSame('3 years ago', $this->elapsed_time_string_formatter->getElapsedTime($this->test_time - 31_557_600 * 3 - 10));
    $this->assertSame('100 years ago', $this->elapsed_time_string_formatter->getElapsedTime($this->test_time - 31_557_600 * 100));
  }
}
