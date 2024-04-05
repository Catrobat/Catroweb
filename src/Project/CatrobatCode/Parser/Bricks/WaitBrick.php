<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class WaitBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::WAIT_BRICK;
    $this->caption = 'Wait _ second(s)';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
