<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class StopRunningStitchBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::STOP_RUNNING_STITCH_BRICK;
    $this->caption = 'Stop running Stitch';
    $this->setImgFile(Constants::EMBROIDERY_BRICK_IMG);
  }
}
