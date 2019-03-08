<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class LegoEV3MotorMoveBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class LegoEV3MotorMoveBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::LEGO_EV3_MOTOR_MOVE_BRICK;

    $formulas = FormulaResolver::resolve($this->brick_xml_properties->formulaList);
    $this->caption = "Set EV3 motor " . $this->brick_xml_properties->motor . " to "
      . $formulas[Constants::LEGO_EV3_POWER_FORMULA] . "% Power for "
      . $formulas[Constants::LEGO_EV3_PERIOD_IN_SECONDS_FORMULA] . " seconds";

    $this->setImgFile(Constants::LEGO_EV3_BRICK_IMG);
  }
}