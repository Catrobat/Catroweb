<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class LookRequestBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::LOOK_REQUEST_BRICK;
    $this->caption = 'Get image from _ and use as current look';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
