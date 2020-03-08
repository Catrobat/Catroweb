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
  /**
   * @var DateTime the freeze time
   */
  public static $freeze_time = null;

  /**
   * Returns the current timestamp or the timestamp of the frozen time if it has been set before.
   *
   * @throws Exception
   *
   * @return int the current timestamp or the timestamp of the frozen time if it has been set before
   */
  public static function getTimestamp()
  {
    return self::getDateTime()->getTimestamp();
  }

  /**
   * Returns the current DateTime or the DateTime of the frozen time if it has been set before.
   *
   * @throws Exception;
   *
   * @return DateTime the current DateTime or the DateTime of the frozen time if it has been set before
   */
  public static function getDateTime()
  {
    if (self::$freeze_time)
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
  public static function freezeTime(DateTime $freeze_time)
  {
    self::$freeze_time = $freeze_time;
  }

  /**
   * Unfreezes the time.
   */
  public static function unfreezeTime()
  {
    self::$freeze_time = null;
  }
}
