<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Scripts;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class StartScript.
 */
class StartScript extends Script
{
  protected function create()
  {
    $this->type = Constants::START_SCRIPT;
    $this->caption = 'When program started';
    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}
