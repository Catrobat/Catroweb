<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class LegoEV3MotorTurnAngleBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class LegoEV3MotorTurnAngleBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::LEGO_EV3_MOTOR_TURN_ANGLE_BRICK;
    $this->caption = "Turn EV3 motor " . $this->brick_xml_properties->motor . " by "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::LEGO_EV3_DEGREES_FORMULA] . "°";

    $this->setImgFile(Constants::LEGO_EV3_BRICK_IMG);
  }
}