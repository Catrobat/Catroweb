<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class LegoEV3MotorStopBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class LegoEV3MotorStopBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::LEGO_EV3_MOTOR_STOP_BRICK;
    $this->caption = "Stop EV3 motor " . $this->brick_xml_properties->motor;

    $this->setImgFile(Constants::LEGO_EV3_BRICK_IMG);
  }
}