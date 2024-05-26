<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class StitchBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::STITCH_BRICK;
    $this->caption = 'Stitch';
    $this->setImgFile(Constants::EMBROIDERY_BRICK_IMG);
  }
}
