<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class SetGravityBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class SetGravityBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::SET_GRAVITY_BRICK;
    $this->caption = "Set gravity for all objects to X: _ Y: _ steps/second²";
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}