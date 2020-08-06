<?php

namespace Tests\phpUnit\Utils;

use App\Utils\TimeUtils;
use DateTime;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \App\Utils\TimeUtils
 */
class TimeUtilsTest extends TestCase
{
  /**
   * Test whether the TimeUtils returns the current timestamp.
   *
   * @test
   *
   * @throws Exception
   */
  public function theCurrentTimestampShouldBeReturned(): void
  {
    $current_timestamp = (new DateTime())->getTimestamp();

    TimeUtils::unfreezeTime();

    $timestamp_from_time_utils = TimeUtils::getTimestamp();

    self::assertEqualsWithDelta($current_timestamp, $timestamp_from_time_utils, 1_000);
  }

  /**
   * Test wheter the TimeUtils returns the current DateTime.
   *
   * @test
   *
   * @throws Exception
   */
  public function theCurrentDatetimeShouldBeReturned(): void
  {
    $current_datetime = new DateTime();

    $datetime_from_time_utils = TimeUtils::getDateTime();

    self::assertEqualsWithDelta($current_datetime->getTimestamp(), $datetime_from_time_utils->getTimestamp(), 1_000);
  }

  /**
   * Freezing the time to a specific value should work.
   *
   * @test
   *
   * @throws Exception
   */
  public function freezingTimeShouldWork(): void
  {
    $freeze_time_to = new DateTime('2012-03-24 17:45:1');

    TimeUtils::freezeTime($freeze_time_to);

    self::assertEqualsWithDelta($freeze_time_to->getTimestamp(), TimeUtils::getTimestamp(), 1_000);
    self::assertEqualsWithDelta($freeze_time_to->getTimestamp(), TimeUtils::getDateTime()->getTimestamp(), 1_000);
  }

  /**
   * Unfreezing the time should work.
   *
   * @test
   *
   * @throws Exception
   */
  public function unfreezingTimeShouldWork(): void
  {
    TimeUtils::unfreezeTime();

    $this->theCurrentTimestampShouldBeReturned();
    $this->theCurrentDatetimeShouldBeReturned();
  }
}
