<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class LegoEV3MotorMoveBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::LEGO_EV3_MOTOR_MOVE_BRICK;
    $this->caption = 'Set EV3 motor _ to _ % Power for _ seconds';
    $this->setImgFile(Constants::LEGO_EV3_BRICK_IMG);
  }
}
