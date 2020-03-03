<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class ContinueSceneBrick.
 */
class ContinueSceneBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::CONTINUE_SCENE_BRICK;
    $this->caption = 'Continue scene _';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
