<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class IfBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::IF_BRICK;
    $this->caption = 'If _ is true then';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
