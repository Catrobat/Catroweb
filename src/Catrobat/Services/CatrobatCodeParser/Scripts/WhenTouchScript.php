<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Scripts;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class WhenTouchScript
 * @package App\Catrobat\Services\CatrobatCodeParser\Scripts
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