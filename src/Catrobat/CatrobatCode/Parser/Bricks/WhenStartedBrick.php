<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;
use App\Catrobat\CatrobatCode\Parser\Scripts\Script;

class WhenStartedBrick extends Script
{
  protected function create(): void
  {
    $this->type = Constants::WHEN_STARTED_BRICK;
    $this->caption = 'When program started';
    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}
