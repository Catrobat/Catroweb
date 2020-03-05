<?php

namespace App\Catrobat\Services\Formatter;

use App\Utils\TimeUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ElapsedTimeStringFormatter.
 */
class ElapsedTimeStringFormatter
{
  /**
   * @var TranslatorInterface
   */
  private $translator;

  /**
   * ElapsedTimeStringFormatter constructor.
   */
  public function __construct(TranslatorInterface $translator)
  {
    $this->translator = $translator;
  }

  /**
   * @param $timestamp
   *
   * @return string
   */
  public function getElapsedTime($timestamp)
  {
    $elapsed = TimeUtils::getTimestamp() - $timestamp;

    if ($elapsed < 0)
    {
      $elapsed = 0;
    }
    if ($elapsed <= 3540)
    {
      $minutes = round($elapsed / 60);

      return $this->translator->trans('time.minutes.ago', ['%count%' => $minutes], 'catroweb');
    }
    if ($elapsed <= 82800)
    {
      $hours = round($elapsed / 3600);

      return $this->translator->trans('time.hours.ago', ['%count%' => $hours], 'catroweb');
    }
    if ($elapsed <= 2505600)
    {
      $days = round($elapsed / 86400);

      return $this->translator->trans('time.days.ago', ['%count%' => $days], 'catroweb');
    }
    if ($elapsed <= 28927800)
    {
      $months = round($elapsed / 2629800);

      return $this->translator->trans('time.months.ago', ['%count%' => $months], 'catroweb');
    }

    $years = round($elapsed / 31557600);

    return $this->translator->trans('time.years.ago', ['%count%' => $years], 'catroweb');
  }
}
