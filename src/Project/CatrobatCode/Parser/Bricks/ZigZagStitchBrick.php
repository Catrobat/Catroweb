<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class ZigZagStitchBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::ZIG_ZAG_STITCH_BRICK;
    $this->caption = 'ZigZag Stitch';
    $this->setImgFile(Constants::EMBROIDERY_BRICK_IMG);
  }
}
