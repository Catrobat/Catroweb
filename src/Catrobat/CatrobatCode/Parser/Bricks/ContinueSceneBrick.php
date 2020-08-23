<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class ContinueSceneBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::CONTINUE_SCENE_BRICK;
    $this->caption = 'Continue scene _';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
