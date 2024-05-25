<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class VibrationBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::VIBRATION_BRICK;
    $this->caption = 'Vibrate for _ second(s)';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
