<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class TripleStitchBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::TRIPLE_STITCH_BRICK;
    $this->caption = 'Triple Stitch';
    $this->setImgFile(Constants::EMBROIDERY_BRICK_IMG);
  }
}
