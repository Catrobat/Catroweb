<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class MoveNStepsBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::MOVE_N_STEPS_BRICK;
    $this->caption = 'Move _ steps';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
