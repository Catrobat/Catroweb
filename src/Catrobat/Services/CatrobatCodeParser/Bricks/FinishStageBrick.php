<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class FinishStageBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::FINISH_STAGE_BRICK;
    $this->caption = 'Finish Stage Brick';
    $this->setImgFile(Constants::FINISH_STAGE_BRICK);
  }
}
