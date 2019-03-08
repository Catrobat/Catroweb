<?php

namespace tests\PhpSpec\spec\App\Catrobat\Services\Formatter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Translation\TranslatorInterface;
use \App\Catrobat\Services\Time;

class ElapsedTimeStringFormatterSpec extends ObjectBehavior
{
  protected $testTime;

  public function let(TranslatorInterface $translator, Time $time)
  {
    $this->testTime = 9999999999999;
    $time->getTime()->willReturn($this->testTime);
    $this->beConstructedWith($translator, $time);
  }

  public function it_is_initializable()
  {
    $this->shouldHaveType('App\Catrobat\Services\Formatter\ElapsedTimeStringFormatter');
  }

  public function it_returns_the_elapsed_time_since_timestamps_in_minutes($translator)
  {
    $translator->transChoice(Argument::exact('time.minutes.ago'), Argument::exact(0), Argument::any(), Argument::any())->willReturn('< 1 minute ago');
    $translator->transChoice(Argument::exact('time.minutes.ago'), Argument::exact(1), Argument::any(), Argument::any())->willReturn('1 minute ago');
    $translator->transChoice(Argument::exact('time.minutes.ago'), Argument::exact(5), Argument::any(), Argument::any())->willReturn('5 minutes ago');
    $translator->transChoice(Argument::exact('time.minutes.ago'), Argument::exact(59), Argument::any(), Argument::any())->willReturn('59 minutes ago');

    $this->getElapsedTime($this->testTime - 10)->shouldReturn('< 1 minute ago');
    $this->getElapsedTime($this->testTime - 80)->shouldReturn('1 minute ago');
    $this->getElapsedTime($this->testTime - 60 * 5)->shouldReturn('5 minutes ago');
    $this->getElapsedTime($this->testTime - 60 * 5 - 10)->shouldReturn('5 minutes ago');
    $this->getElapsedTime($this->testTime - 60 * 59)->shouldReturn('59 minutes ago');
  }

  public function it_returns_the_elapsed_time_since_timestamps_in_hours($translator)
  {
    $translator->transChoice(Argument::exact('time.hours.ago'), Argument::exact(1), Argument::any(), Argument::any())->willReturn('1 hour ago');
    $translator->transChoice(Argument::exact('time.hours.ago'), Argument::exact(5), Argument::any(), Argument::any())->willReturn('5 hours ago');
    $translator->transChoice(Argument::exact('time.hours.ago'), Argument::exact(23), Argument::any(), Argument::any())->willReturn('23 hours ago');

    $this->getElapsedTime($this->testTime - 3600)->shouldReturn('1 hour ago');
    $this->getElapsedTime($this->testTime - 3600 * 5)->shouldReturn('5 hours ago');
    $this->getElapsedTime($this->testTime - 3600 * 5 - 10)->shouldReturn('5 hours ago');
    $this->getElapsedTime($this->testTime - 3600 * 23)->shouldReturn('23 hours ago');
  }

  public function it_returns_the_elapsed_time_since_timestamps_in_days($translator)
  {
    $translator->transChoice(Argument::exact('time.days.ago'), Argument::exact(1), Argument::any(), Argument::any())->willReturn('1 day ago');
    $translator->transChoice(Argument::exact('time.days.ago'), Argument::exact(5), Argument::any(), Argument::any())->willReturn('5 days ago');
    $translator->transChoice(Argument::exact('time.days.ago'), Argument::exact(6), Argument::any(), Argument::any())->willReturn('6 days ago');

    $this->getElapsedTime($this->testTime - 86400)->shouldReturn('1 day ago');
    $this->getElapsedTime($this->testTime - 86400 * 5)->shouldReturn('5 days ago');
    $this->getElapsedTime($this->testTime - 86400 * 5 - 10)->shouldReturn('5 days ago');
    $this->getElapsedTime($this->testTime - 86400 * 6)->shouldReturn('6 days ago');
  }

  public function it_returns_the_elapsed_time_since_timestamps_in_months($translator)
  {
    $translator->transChoice(Argument::exact('time.months.ago'), Argument::exact(1), Argument::any(), Argument::any())->willReturn('1 month ago');
    $translator->transChoice(Argument::exact('time.months.ago'), Argument::exact(6), Argument::any(), Argument::any())->willReturn('6 months ago');
    $translator->transChoice(Argument::exact('time.months.ago'), Argument::exact(11), Argument::any(), Argument::any())->willReturn('11 months ago');

    $this->getElapsedTime($this->testTime - 2629800)->shouldReturn('1 month ago');
    $this->getElapsedTime($this->testTime - 2629800 * 6)->shouldReturn('6 months ago');
    $this->getElapsedTime($this->testTime - 2629800 * 6 - 10)->shouldReturn('6 months ago');
    $this->getElapsedTime($this->testTime - 2629800 * 11)->shouldReturn('11 months ago');
  }

  public function it_returns_the_elapsed_time_since_timestamps_in_years($translator)
  {
    $translator->transChoice(Argument::exact('time.years.ago'), Argument::exact(1), Argument::any(), Argument::any())->willReturn('1 year ago');
    $translator->transChoice(Argument::exact('time.years.ago'), Argument::exact(3), Argument::any(), Argument::any())->willReturn('3 years ago');
    $translator->transChoice(Argument::exact('time.years.ago'), Argument::exact(100), Argument::any(), Argument::any())->willReturn('100 years ago');

    $this->getElapsedTime($this->testTime - 31557600)->shouldReturn('1 year ago');
    $this->getElapsedTime($this->testTime - 31557600 * 3)->shouldReturn('3 years ago');
    $this->getElapsedTime($this->testTime - 31557600 * 3 - 10)->shouldReturn('3 years ago');
    $this->getElapsedTime($this->testTime - 31557600 * 100)->shouldReturn('100 years ago');
  }
}
