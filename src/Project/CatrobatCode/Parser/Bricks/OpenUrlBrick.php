<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class OpenUrlBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::OPEN_URL_BRICK;
    $this->caption = 'Open _ in browser';
    $this->setImgFile(Constants::DEVICE_BRICK_IMG);
  }
}
