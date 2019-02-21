<?php

namespace Catrobat\Behat\TwigReportExtension\facades;

/**
 * Interface StepInterface
 * @package Catrobat\Behat\TwigReportExtension\facades
 */
interface StepInterface
{

  /**
   * @return mixed
   */
  public function getText();

  /**
   * @return mixed
   */
  public function getResult();

  /**
   * @return mixed
   */
  public function getArguments();

  /**
   * @return mixed
   */
  public function getLine();
}