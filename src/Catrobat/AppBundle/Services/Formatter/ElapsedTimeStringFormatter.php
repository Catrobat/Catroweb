<?php

namespace Catrobat\AppBundle\Services\Formatter;

use Catrobat\AppBundle\Services\Time;
use Symfony\Component\Translation\TranslatorInterface;

class ElapsedTimeStringFormatter
{
  private $translator;
  private $time;

  public function __construct(TranslatorInterface $translator, Time $time)
  {
    $this->translator = $translator;
    $this->time = $time;
  }

  public function getElapsedTime($timestamp)
  {
    $elapsed = $this->time->getTime() - $timestamp;

    if ($elapsed <= 3540)
    {
      $minutes = round($elapsed / 60);

      return $this->translator->transChoice('time.minutes.ago', $minutes, ['%count%' => $minutes], 'catroweb');
    }
    elseif ($elapsed <= 82800)
    {
      $hours = round($elapsed / 3600);

      return $this->translator->transChoice('time.hours.ago', $hours, ['%count%' => $hours], 'catroweb');
    }
    elseif ($elapsed <= 2505600)
    {
      $days = round($elapsed / 86400);

      return $this->translator->transChoice('time.days.ago', $days, ['%count%' => $days], 'catroweb');
    }
    elseif ($elapsed <= 28927800)
    {
      $months = round($elapsed / 2629800);

      return $this->translator->transChoice('time.months.ago', $months, ['%count%' => $months], 'catroweb');
    }

    $years = round($elapsed / 31557600);

    return $this->translator->transChoice('time.years.ago', $years, ['%count%' => $years], 'catroweb');
  }
}
