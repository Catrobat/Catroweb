<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class GoNStepsBackBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::GO_N_STEPS_BACK_BRICK;
    $this->caption = 'Go back _ layer';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
