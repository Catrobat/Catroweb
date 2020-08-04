<?php

namespace App\Utils;

use Exception;
use Symfony\Contracts\Translation\TranslatorInterface;

class ElapsedTimeStringFormatter
{
  private TranslatorInterface $translator;

  public function __construct(TranslatorInterface $translator)
  {
    $this->translator = $translator;
  }

  /**
   * @param mixed $timestamp
   *
   * @throws Exception
   */
  public function getElapsedTime($timestamp): string
  {
    $elapsed = TimeUtils::getTimestamp() - $timestamp;

    if ($elapsed < 0)
    {
      $elapsed = 0;
    }
    if ($elapsed <= 3_540)
    {
      $minutes = (int) round($elapsed / 60);

      return $this->translator->trans('time.minutes.ago', ['%count%' => $minutes], 'catroweb');
    }
    if ($elapsed <= 82_800)
    {
      $hours = (int) round($elapsed / 3_600);

      return $this->translator->trans('time.hours.ago', ['%count%' => $hours], 'catroweb');
    }
    if ($elapsed <= 2_505_600)
    {
      $days = (int) round($elapsed / 86_400);

      return $this->translator->trans('time.days.ago', ['%count%' => $days], 'catroweb');
    }
    if ($elapsed <= 28_927_800)
    {
      $months = (int) round($elapsed / 2_629_800);

      return $this->translator->trans('time.months.ago', ['%count%' => $months], 'catroweb');
    }

    $years = (int) round($elapsed / 31_557_600);

    return $this->translator->trans('time.years.ago', ['%count%' => $years], 'catroweb');
  }
}
