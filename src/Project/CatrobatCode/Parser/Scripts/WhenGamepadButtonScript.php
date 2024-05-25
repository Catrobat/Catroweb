<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Scripts;

use App\Project\CatrobatCode\Parser\Constants;

class WhenGamepadButtonScript extends Script
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::WHEN_GAME_PAD_BUTTON_SCRIPT;
    $this->caption = 'When gamepad button _ pressed';
    $this->setImgFile(Constants::CONTROL_SCRIPT_IMG);
  }
}
