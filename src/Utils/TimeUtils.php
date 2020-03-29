<?php

namespace App\Utils;

use DateTime;
use Exception;

/**
 * Class TimeUtils.
 *
 * Used for providing a unique time source throughout the whole project. The time can be frozen to a specific value
 * for testing purposes.
 */
class TimeUtils
{
  public static ?DateTime $freeze_time = null;

  /**
   * Returns the current timestamp or the timestamp of the frozen time if it has been set before.
   *
   * @throws Exception
   */
  public static function getTimestamp(): int
  {
    return self::getDateTime()->getTimestamp();
  }

  /**
   * Returns the current DateTime or the DateTime of the frozen time if it has been set before.
   *
   * @throws Exception;
   */
  public static function getDateTime(): DateTime
  {
    if (null !== self::$freeze_time)
    {
      return self::$freeze_time;
    }

    return new DateTime();
  }

  /**
   * Freezes the time.
   *
   * @param DateTime $freeze_time the desired freeze time
   */
  public static function freezeTime(DateTime $freeze_time): void
  {
    self::$freeze_time = $freeze_time;
  }

  /**
   * Unfreezes the time.
   */
  public static function unfreezeTime(): void
  {
    self::$freeze_time = null;
  }
}
