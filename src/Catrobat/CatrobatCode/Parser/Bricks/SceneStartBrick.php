<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class SceneStartBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SCENE_START_BRICK;
    $this->caption = 'Start scene _';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
