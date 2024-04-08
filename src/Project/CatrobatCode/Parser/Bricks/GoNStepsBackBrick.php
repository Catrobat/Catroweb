<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class GoNStepsBackBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::GO_N_STEPS_BACK_BRICK;
    $this->caption = 'Go back _ layer';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
