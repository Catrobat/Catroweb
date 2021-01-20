<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;
use App\Catrobat\CatrobatCode\Parser\Scripts\Script;

class WhenGamepadButtonBrick extends Script
{
  protected function create(): void
  {
    $this->type = Constants::WHEN_GAME_PAD_BUTTON_BRICK;
    $this->caption = 'When gamepad button _ pressed';
    $this->setImgFile(Constants::CONTROL_SCRIPT_IMG);
  }
}
