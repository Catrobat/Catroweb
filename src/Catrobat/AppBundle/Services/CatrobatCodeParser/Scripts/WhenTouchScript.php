<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Scripts;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

/**
 * Class WhenTouchScript
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Scripts
 */
class WhenTouchScript extends Script
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::WHEN_TOUCH_SCRIPT;
    $this->caption = "When screen is touched";

    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}