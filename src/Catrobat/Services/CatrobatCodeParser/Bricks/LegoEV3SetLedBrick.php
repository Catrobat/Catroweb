<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class LegoEV3SetLedBrick.
 */
class LegoEV3SetLedBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::LEGO_EV3_SET_LED_BRICK;
    $this->caption = 'Set EV3 LED Status';
    $this->setImgFile(Constants::LEGO_EV3_BRICK_IMG);
  }
}
