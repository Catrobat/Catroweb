<?php

namespace tests\PhpSpec\spec\App\Catrobat\Services\Formatter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Utils\TimeUtils;
use \DateTime;
use \Exception;

/**
 * Class ElapsedTimeStringFormatterSpec
 * @package tests\PhpSpec\spec\App\Catrobat\Services\Formatter
 */
class ElapsedTimeStringFormatterSpec extends ObjectBehavior
{
  /**
   * @var
   */
  protected $testTime;

  /**
   * @param TranslatorInterface $translator
   * @throws Exception
   */
  public function let(TranslatorInterface $translator)
  {
    TimeUtils::freezeTime(new DateTime('2015-10-26 13:33:37'));
    $this->testTime = TimeUtils::getTimestamp();

    $this->beConstructedWith($translator);
  }

  /**
   *
   */
  public function it_is_initializable()
  {
    $this->shouldHaveType('App\Catrobat\Services\Formatter\ElapsedTimeStringFormatter');
  }

  /**
   * @param $translator TranslatorInterface
   */
  public function it_returns_the_elapsed_time_since_timestamps_in_minutes($translator)
  {
    $translator->trans(Argument::exact('time.minutes.ago'), ['%count%' => 0], Argument::any(), Argument::any())->willReturn('< 1 minute ago');
    $translator->trans(Argument::exact('time.minutes.ago'), ['%count%' => 1], Argument::any(), Argument::any())->willReturn('1 minute ago');
    $translator->trans(Argument::exact('time.minutes.ago'), ['%count%' => 5], Argument::any(), Argument::any())->willReturn('5 minutes ago');
    $translator->trans(Argument::exact('time.minutes.ago'), ['%count%' => 59], Argument::any(), Argument::any())->willReturn('59 minutes ago');

    $this->getElapsedTime($this->testTime + 10 )->shouldReturn('< 1 minute ago');
    $this->getElapsedTime($this->testTime)->shouldReturn('< 1 minute ago');
    $this->getElapsedTime($this->testTime - 10)->shouldReturn('< 1 minute ago');
    $this->getElapsedTime($this->testTime - 80)->shouldReturn('1 minute ago');
    $this->getElapsedTime($this->testTime - 60 * 5)->shouldReturn('5 minutes ago');
    $this->getElapsedTime($this->testTime - 60 * 5 - 10)->shouldReturn('5 minutes ago');
    $this->getElapsedTime($this->testTime - 60 * 59)->shouldReturn('59 minutes ago');
  }

  /**
   * @param $translator TranslatorInterface
   */
  public function it_returns_the_elapsed_time_since_timestamps_in_hours($translator)
  {
    $translator->trans(Argument::exact('time.hours.ago'), ['%count%' => 1], Argument::any(), Argument::any())->willReturn('1 hour ago');
    $translator->trans(Argument::exact('time.hours.ago'), ['%count%' => 5], Argument::any(), Argument::any())->willReturn('5 hours ago');
    $translator->trans(Argument::exact('time.hours.ago'), ['%count%' => 23], Argument::any(), Argument::any())->willReturn('23 hours ago');

    $this->getElapsedTime($this->testTime - 3600)->shouldReturn('1 hour ago');
    $this->getElapsedTime($this->testTime - 3600 * 5)->shouldReturn('5 hours ago');
    $this->getElapsedTime($this->testTime - 3600 * 5 - 10)->shouldReturn('5 hours ago');
    $this->getElapsedTime($this->testTime - 3600 * 23)->shouldReturn('23 hours ago');
  }

  public function it_returns_the_elapsed_time_since_timestamps_in_days($translator)
  {
    $translator->trans(Argument::exact('time.days.ago'), ['%count%' => 1], Argument::any(), Argument::any())->willReturn('1 day ago');
    $translator->trans(Argument::exact('time.days.ago'), ['%count%' => 5], Argument::any(), Argument::any())->willReturn('5 days ago');
    $translator->trans(Argument::exact('time.days.ago'), ['%count%' => 6], Argument::any(), Argument::any())->willReturn('6 days ago');

    $this->getElapsedTime($this->testTime - 86400)->shouldReturn('1 day ago');
    $this->getElapsedTime($this->testTime - 86400 * 5)->shouldReturn('5 days ago');
    $this->getElapsedTime($this->testTime - 86400 * 5 - 10)->shouldReturn('5 days ago');
    $this->getElapsedTime($this->testTime - 86400 * 6)->shouldReturn('6 days ago');
  }

  public function it_returns_the_elapsed_time_since_timestamps_in_months($translator)
  {
    $translator->trans(Argument::exact('time.months.ago'), ['%count%' => 1], Argument::any(), Argument::any())->willReturn('1 month ago');
    $translator->trans(Argument::exact('time.months.ago'), ['%count%' => 6], Argument::any(), Argument::any())->willReturn('6 months ago');
    $translator->trans(Argument::exact('time.months.ago'), ['%count%' => 11], Argument::any(), Argument::any())->willReturn('11 months ago');

    $this->getElapsedTime($this->testTime - 2629800)->shouldReturn('1 month ago');
    $this->getElapsedTime($this->testTime - 2629800 * 6)->shouldReturn('6 months ago');
    $this->getElapsedTime($this->testTime - 2629800 * 6 - 10)->shouldReturn('6 months ago');
    $this->getElapsedTime($this->testTime - 2629800 * 11)->shouldReturn('11 months ago');
  }

  public function it_returns_the_elapsed_time_since_timestamps_in_years($translator)
  {
    $translator->trans(Argument::exact('time.years.ago'), ['%count%' => 1], Argument::any(), Argument::any())->willReturn('1 year ago');
    $translator->trans(Argument::exact('time.years.ago'), ['%count%' => 3], Argument::any(), Argument::any())->willReturn('3 years ago');
    $translator->trans(Argument::exact('time.years.ago'), ['%count%' => 100], Argument::any(), Argument::any())->willReturn('100 years ago');

    $this->getElapsedTime($this->testTime - 31557600)->shouldReturn('1 year ago');
    $this->getElapsedTime($this->testTime - 31557600 * 3)->shouldReturn('3 years ago');
    $this->getElapsedTime($this->testTime - 31557600 * 3 - 10)->shouldReturn('3 years ago');
    $this->getElapsedTime($this->testTime - 31557600 * 100)->shouldReturn('100 years ago');
  }
}
