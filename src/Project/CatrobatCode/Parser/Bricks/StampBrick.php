<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class StampBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::STAMP_BRICK;
    $this->caption = 'Stamp';
    $this->setImgFile(Constants::PEN_BRICK_IMG);
  }
}
