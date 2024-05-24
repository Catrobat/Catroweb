<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class LegoNxtMotorStopBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::LEGO_NXT_MOTOR_STOP_BRICK;
    $this->caption = 'Stop NXT motor';
    $this->setImgFile(Constants::LEGO_NXT_BRICK_IMG);
  }
}
