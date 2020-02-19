<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class SceneStartBrick.
 */
class SceneStartBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::SCENE_START_BRICK;
    $this->caption = 'Start scene _';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
