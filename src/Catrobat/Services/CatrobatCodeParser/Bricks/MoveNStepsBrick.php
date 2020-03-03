<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class MoveNStepsBrick.
 */
class MoveNStepsBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::MOVE_N_STEPS_BRICK;
    $this->caption = 'Move _ steps';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
