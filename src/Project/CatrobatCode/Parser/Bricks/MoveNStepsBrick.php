<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class MoveNStepsBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::MOVE_N_STEPS_BRICK;
    $this->caption = 'Move _ steps';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
