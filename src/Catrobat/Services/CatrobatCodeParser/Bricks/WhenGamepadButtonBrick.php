<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\Scripts\Script;

class WhenGamepadButtonBrick extends Script
{
  protected function create(): void
  {
    $this->type = Constants::WHEN_GAME_PAD_BUTTON_BRICK;
    $this->caption = 'When gamepad button _ pressed';
    $this->setImgFile(Constants::CONTROL_SCRIPT_IMG);
  }
}
