<?php

namespace tests;

use \DateTime;
use App\Utils\TimeUtils;
use \PHPUnit\Framework\TestCase;

class TimeUtilsTest extends TestCase
{
  /**
   * Test wheter the TimeUtils returns the current timestamp.
   *
   * @test
   */
  public function the_current_timestamp_should_be_returned()
  {
  $current_timestamp = (new DateTime())->getTimestamp();

  $timestamp_from_time_utils = TimeUtils::getTimestamp();

  self::assertEqualsWithDelta($current_timestamp, $timestamp_from_time_utils, 1000);
  }

  /**
   * Test wheter the TimeUtils returns the current DateTime.
   *
   * @test
   */
  public function the_current_datetime_should_be_returned()
  {
    $current_datetime = new DateTime();

    $datetime_from_time_utils = TimeUtils::getDateTime();

    self::assertEqualsWithDelta($current_datetime->getTimestamp(), $datetime_from_time_utils->getTimestamp(), 1000);
  }

  /**
   * Freezing the time to a specific value should work.
   *
   * @test
   */
  public function freezing_time_should_work()
  {
    $freeze_time_to = new DateTime('2012-03-24 17:45:1');

    TimeUtils::freezeTime($freeze_time_to);

    self::assertEqualsWithDelta($freeze_time_to->getTimestamp(), TimeUtils::getTimestamp(), 1000);
    self::assertEqualsWithDelta($freeze_time_to->getTimestamp(), TimeUtils::getDateTime()->getTimestamp(), 1000);
  }

  /**
   * Unfreezing the time should work.
   *
   * @test
   */
  public function unfreezing_time_should_work()
  {
    TimeUtils::unfreezeTime();

    $this->the_current_timestamp_should_be_returned();
    $this->the_current_datetime_should_be_returned();
  }
}