<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

class LegoEV3MotorStopBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::LEGO_EV3_MOTOR_STOP_BRICK;
    $this->caption = "Stop EV3 motor " . $this->brick_xml_properties->motor;

    $this->setImgFile(Constants::LEGO_EV3_BRICK_IMG);
  }
}