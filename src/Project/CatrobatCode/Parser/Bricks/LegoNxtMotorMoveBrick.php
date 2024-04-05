<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class LegoNxtMotorMoveBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::LEGO_NXT_MOTOR_MOVE_BRICK;
    $this->caption = 'Set NXT motor';
    $this->setImgFile(Constants::LEGO_NXT_BRICK_IMG);
  }
}
