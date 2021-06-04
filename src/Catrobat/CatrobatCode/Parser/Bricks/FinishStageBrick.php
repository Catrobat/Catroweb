<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class FinishStageBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::FINISH_STAGE_BRICK;
    $this->caption = 'Finish Stage Brick';
    $this->setImgFile(Constants::FINISH_STAGE_BRICK);
  }
}
