<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Utils;

use App\Utils\TimeUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TimeUtils::class)]
class TimeUtilsTest extends TestCase
{
  /**
   * Test whether the TimeUtils returns the current timestamp.
   *
   * @throws \Exception
   */
  public function testTheCurrentTimestampShouldBeReturned(): void
  {
    $current_timestamp = (new \DateTime())->getTimestamp();

    TimeUtils::unfreezeTime();

    $timestamp_from_time_utils = TimeUtils::getTimestamp();

    self::assertEqualsWithDelta($current_timestamp, $timestamp_from_time_utils, 1_000);
  }

  /**
   * Test whether the TimeUtils returns the current DateTime.
   *
   * @throws \Exception
   */
  public function testTheCurrentDatetimeShouldBeReturned(): void
  {
    $current_datetime = new \DateTime();

    $datetime_from_time_utils = TimeUtils::getDateTime();

    self::assertEqualsWithDelta($current_datetime->getTimestamp(), $datetime_from_time_utils->getTimestamp(), 1_000);
  }

  /**
   * Freezing the time to a specific value should work.
   *
   * @throws \Exception
   */
  public function testFreezingTimeShouldWork(): void
  {
    $freeze_time_to = new \DateTime('2012-03-24 17:45:1');

    TimeUtils::freezeTime($freeze_time_to);

    self::assertEqualsWithDelta($freeze_time_to->getTimestamp(), TimeUtils::getTimestamp(), 1_000);
    self::assertEqualsWithDelta($freeze_time_to->getTimestamp(), TimeUtils::getDateTime()->getTimestamp(), 1_000);
  }

  /**
   * Unfreezing the time should work.
   *
   * @throws \Exception
   */
  public function testUnfreezingTimeShouldWork(): void
  {
    TimeUtils::unfreezeTime();

    $this->testTheCurrentDatetimeShouldBeReturned();
    $this->testTheCurrentDatetimeShouldBeReturned();
  }
}
