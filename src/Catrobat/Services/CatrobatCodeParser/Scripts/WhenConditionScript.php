<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Scripts;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class WhenConditionScript.
 */
class WhenConditionScript extends Script
{
  protected function create()
  {
    $this->type = Constants::WHEN_CONDITION_SCRIPT;
    $this->caption = 'When _ becomes true';
    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}
