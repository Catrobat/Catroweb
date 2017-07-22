<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

class LegoEV3MotorMoveBrick extends Brick
{
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