<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Scripts;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

/**
 * Class UnknownScript
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Scripts
 */
class UnknownScript extends Script
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::UNKNOWN_SCRIPT;
    $this->caption = "Unknown Script";

    $this->setImgFile(Constants::UNKNOWN_SCRIPT_IMG);
  }
}