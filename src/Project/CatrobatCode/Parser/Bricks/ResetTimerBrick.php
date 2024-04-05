<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class ResetTimerBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::RESET_TIMER_BRICK;
    $this->caption = 'Reset timer';
    $this->setImgFile(Constants::DEVICE_BRICK_IMG);
  }
}
