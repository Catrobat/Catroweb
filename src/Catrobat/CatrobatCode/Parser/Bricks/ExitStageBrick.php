<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class ExitStageBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::EXIT_STAGE_BRICK;
    $this->caption = 'Exit Stage Brick';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
