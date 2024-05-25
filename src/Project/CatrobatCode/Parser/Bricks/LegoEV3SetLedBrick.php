<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class LegoEV3SetLedBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::LEGO_EV3_SET_LED_BRICK;
    $this->caption = 'Set EV3 LED Status';
    $this->setImgFile(Constants::LEGO_EV3_BRICK_IMG);
  }
}
