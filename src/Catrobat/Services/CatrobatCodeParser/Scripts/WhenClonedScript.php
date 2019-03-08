<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Scripts;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class WhenClonedScript
 * @package App\Catrobat\Services\CatrobatCodeParser\Scripts
 */
class WhenClonedScript extends Script
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::WHEN_CLONED_SCRIPT;
    $this->caption = "When I start as a clone";

    $this->setImgFile(Constants::CONTROL_SCRIPT_IMG);
  }
}