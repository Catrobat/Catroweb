<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class RaspiPwmBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class RaspiPwmBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::RASPI_PWM_BRICK;
    $this->caption = "Set Raspberry Pi PWM pin _ to _";
    $this->setImgFile(Constants::RASPI_BRICK_IMG);
  }
}