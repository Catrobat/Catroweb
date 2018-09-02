<?php

namespace Catrobat\AppBundle\Features\Driver;

use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\DriverException;
use Symfony\Component\Process\Process;
use WebDriver\WebDriver;

class PhantomJsDriver extends Selenium2Driver
{
  /**
   * @var Symfony\Component\Process\Process
   */
  protected $phantomJsProc;

  /**
   * @var string
   */
  protected $phantomJsBin;

  /**
   * @var integer
   */
  protected $wdPort;

  /**
   * @var Pid (process identifier)
   */
  protected $processId;

  /**
   * Instantiates the driver.
   *
   * @param string  $browserName         Browser name
   * @param array   $desiredCapabilities The desired capabilities
   * @param string  $wdHost              The WebDriver host
   * @param integer $wdPort              The WebDriver port
   * @param string  $bin                 The path to PhantomJS binary
   */
  public function __construct(
    $browserName = 'firefox',
    $desiredCapabilities = null,
    $wdHost = 'http://localhost:8643/wd/hub',
    $wdPort = 8643,
    $bin = '/usr/local/bin/phantomjs'
  )
  {
    $this->setBrowserName($browserName);
    $this->setDesiredCapabilities($desiredCapabilities);
    $this->setWebDriver(new WebDriver($wdHost));
    $this->wdPort = $wdPort;
    $this->phantomJsBin = $bin;
  }

  /**
   * Starts the driver with an exec command and sets the processId.
   */
  public function start()
  {
    if ($this->phantomJsProc !== null)
    {
      return parent::start();
    }

    $cmd = sprintf('exec %s --webdriver=%d', $this->phantomJsBin, $this->wdPort);

    try
    {
      $this->phantomJsProc = new Process($cmd);
      $this->phantomJsProc->start();
      $this->processId = $this->phantomJsProc->getPid();
    } catch (\Exception $e)
    {
      throw new DriverException('Could not start PhantomJs', 0, $e);
    }

    if (!$this->phantomJsProc->isRunning())
    {
      throw new DriverException('Could not confirm PhantomJs is running');
    }

    // give PhantomJs a chance to start before creating a session
    sleep(1);
    parent::start();
  }

  /**
   * Checks if the driver is running.
   * @return true if the driver is running and false otherwise
   */
  public function isRunning()
  {
    if ($this->phantomJsProc !== null && $this->phantomJsProc->isRunning())
    {
      return true;
    }

    return false;
  }

  /**
   * Stops the driver with posix_kill.
   */
  public function stop()
  {
    try
    {
      //parent::stop();
      //$this->phantomJsProc->stop(0);

      posix_kill($this->processId, 0);
    } catch (\Exception $e)
    {
      throw new Exception('Could not stop PhantomJs process');
    }
  }
}