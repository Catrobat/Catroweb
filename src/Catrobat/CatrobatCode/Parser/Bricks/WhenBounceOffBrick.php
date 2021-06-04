<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;
use App\Catrobat\CatrobatCode\Parser\Scripts\Script;

class WhenBounceOffBrick extends Script
{
  protected function create(): void
  {
    $this->type = Constants::WHEN_BOUNCE_OFF_BRICK;
    $this->caption = 'When you bounce off';
    $this->setImgFile(Constants::MOTION_SCRIPT_IMG);
  }
}
