<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class WhenBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::WHEN_BRICK;
    $this->caption = 'When tapped';
    $this->setImgFile(Constants::EVENT_BRICK_IMG);
  }
}
