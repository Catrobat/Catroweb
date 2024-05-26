<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class RepeatBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::REPEAT_BRICK;
    $this->caption = 'Repeat _ times';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
