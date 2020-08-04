<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;
use App\Catrobat\CatrobatCode\Parser\Scripts\Script;

class WhenClonedBrick extends Script
{
  protected function create(): void
  {
    $this->type = Constants::WHEN_CLONED_BRICK;
    $this->caption = 'When I start as a clone';
    $this->setImgFile(Constants::CONTROL_SCRIPT_IMG);
  }
}
