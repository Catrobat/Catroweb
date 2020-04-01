<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\Scripts\Script;

class WhenNfcBrick extends Script
{
  protected function create(): void
  {
    $this->type = Constants::WHEN_NFC_BRICK;
    $this->caption = 'When NFC';
    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}
