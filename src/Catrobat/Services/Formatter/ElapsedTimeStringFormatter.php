<?php

namespace App\Catrobat\Services\Formatter;

use App\Catrobat\Services\Time;
use Symfony\Contracts\Translation\TranslatorInterface;


/**
 * Class ElapsedTimeStringFormatter
 * @package App\Catrobat\Services\Formatter
 */
class ElapsedTimeStringFormatter
{
  /**
   * @var TranslatorInterface
   */
  private $translator;

  /**
   * @var Time
   */
  private $time;

  /**
   * ElapsedTimeStringFormatter constructor.
   *
   * @param TranslatorInterface $translator
   * @param Time                $time
   */
  public function __construct(TranslatorInterface $translator, Time $time)
  {
    $this->translator = $translator;
    $this->time = $time;
  }

  /**
   * @param $timestamp
   *
   * @return string
   */
  public function getElapsedTime($timestamp)
  {
    $elapsed = $this->time->getTime() - $timestamp;

    if ($elapsed <= 3540)
    {
      $minutes = round($elapsed / 60);

      return $this->translator->trans('time.minutes.ago', ['%count%' => $minutes], 'catroweb');
    }
    elseif ($elapsed <= 82800)
    {
      $hours = round($elapsed / 3600);

      return $this->translator->trans('time.hours.ago', ['%count%' => $hours], 'catroweb');
    }
    elseif ($elapsed <= 2505600)
    {
      $days = round($elapsed / 86400);

      return $this->translator->trans('time.days.ago', ['%count%' => $days], 'catroweb');
    }
    elseif ($elapsed <= 28927800)
    {
      $months = round($elapsed / 2629800);

      return $this->translator->trans('time.months.ago', ['%count%' => $months], 'catroweb');
    }

    $years = round($elapsed / 31557600);

    return $this->translator->trans('time.years.ago', ['%count%' => $years], 'catroweb');
  }
}
