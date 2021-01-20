<?php

namespace App\Catrobat\CatrobatCode\Parser\Scripts;

use App\Catrobat\CatrobatCode\Parser\Constants;

class WhenClonedScript extends Script
{
  protected function create(): void
  {
    $this->type = Constants::WHEN_CLONED_SCRIPT;
    $this->caption = 'When I start as a clone';
    $this->setImgFile(Constants::CONTROL_SCRIPT_IMG);
  }
}
