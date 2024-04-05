<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class RepeatUntilBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::REPEAT_UNTIL_BRICK;
    $this->caption = 'Repeat until _ is true';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
