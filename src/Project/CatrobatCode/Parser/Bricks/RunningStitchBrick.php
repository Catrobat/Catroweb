<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class RunningStitchBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::RUNNING_STITCH_BRICK;
    $this->caption = 'Stitch is running';
    $this->setImgFile(Constants::EMBROIDERY_BRICK_IMG);
  }
}
