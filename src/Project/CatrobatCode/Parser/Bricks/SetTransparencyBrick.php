<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class SetTransparencyBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::SET_TRANSPARENCY_BRICK;
    $this->caption = 'Set transparency to _ %';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
