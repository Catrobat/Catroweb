<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class StopRunningStitchBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::STOP_RUNNING_STITCH_BRICK;
    $this->caption = 'Stop running Stitch';
    $this->setImgFile(Constants::EMBROIDERY_BRICK_IMG);
  }
}
