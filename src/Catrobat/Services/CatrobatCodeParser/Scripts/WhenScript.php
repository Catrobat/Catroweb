<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Scripts;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class WhenScript
 * @package App\Catrobat\Services\CatrobatCodeParser\Scripts
 */
class WhenScript extends Script
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::WHEN_SCRIPT;
    $this->caption = "When tapped";

    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}