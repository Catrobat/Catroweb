<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;
use App\Project\CatrobatCode\Parser\Scripts\Script;

class WhenGamepadButtonBrick extends Script
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::WHEN_GAME_PAD_BUTTON_BRICK;
    $this->caption = 'When gamepad button _ pressed';
    $this->setImgFile(Constants::CONTROL_SCRIPT_IMG);
  }
}
