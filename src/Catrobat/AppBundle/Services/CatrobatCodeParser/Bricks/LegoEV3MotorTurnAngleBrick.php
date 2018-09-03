<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

class LegoEV3MotorTurnAngleBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::LEGO_EV3_MOTOR_TURN_ANGLE_BRICK;
    $this->caption = "Turn EV3 motor " . $this->brick_xml_properties->motor . " by "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::LEGO_EV3_DEGREES_FORMULA] . "°";

    $this->setImgFile(Constants::LEGO_EV3_BRICK_IMG);
  }
}