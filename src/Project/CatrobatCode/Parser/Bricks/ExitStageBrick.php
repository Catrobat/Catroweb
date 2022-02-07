<?php

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class ExitStageBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::EXIT_STAGE_BRICK;
    $this->caption = 'Exit Stage Brick';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
