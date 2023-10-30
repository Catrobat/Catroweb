<?php

namespace App\Utils;

use Symfony\Contracts\Translation\TranslatorInterface;

class ElapsedTimeStringFormatter
{
  public function __construct(
    private readonly TranslatorInterface $translator
  ) {
  }

  /**
   * @throws \Exception
   */
  public function format(mixed $timestamp): string
  {
    $elapsed = max(0, TimeUtils::getTimestamp() - $timestamp);

    if ($elapsed <= 3_540) {
      return $this->getFormattedInMinutes((int) round($elapsed / 60));
    }
    if ($elapsed <= 82_800) {
      return $this->getFormattedInHours((int) round($elapsed / 3_600));
    }
    if ($elapsed <= 2_505_600) {
      return $this->getFormattedInDays((int) round($elapsed / 86_400));
    }
    if ($elapsed <= 28_927_800) {
      return $this->getFormattedInMonths((int) round($elapsed / 2_629_800));
    }

    return $this->getFormattedInYears((int) round($elapsed / 31_557_600));
  }

  protected function getFormattedInMinutes(int $minutes): string
  {
    return $this->translator->trans('time.minutes.ago', ['%count%' => $minutes], 'catroweb');
  }

  protected function getFormattedInHours(int $hours): string
  {
    return $this->translator->trans('time.hours.ago', ['%count%' => $hours], 'catroweb');
  }

  protected function getFormattedInDays(int $days): string
  {
    return $this->translator->trans('time.days.ago', ['%count%' => $days], 'catroweb');
  }

  protected function getFormattedInMonths(int $months): string
  {
    return $this->translator->trans('time.months.ago', ['%count%' => $months], 'catroweb');
  }

  protected function getFormattedInYears(int $years): string
  {
    return $this->translator->trans('time.years.ago', ['%count%' => $years], 'catroweb');
  }
}
