<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class ComeToFrontBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::COME_TO_FRONT_BRICK;
    $this->caption = 'Go to front';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
