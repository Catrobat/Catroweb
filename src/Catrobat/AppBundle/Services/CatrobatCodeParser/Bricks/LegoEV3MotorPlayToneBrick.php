<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

class LegoEV3MotorPlayToneBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::LEGO_EV3_MOTOR_PLAY_TONE_BRICK;

    $formulas = FormulaResolver::resolve($this->brick_xml_properties->formulaList);
    $this->caption = "Play EV3 tone for " . $formulas[Constants::LEGO_EV3_DURATION_IN_SECONDS_FORMULA]
      . " seconds - Frequency: " . $formulas[Constants::LEGO_EV3_FREQUENCY_FORMULA]
      . " x100Hz - Volume: " . $formulas[Constants::LEGO_EV3_VOLUME_FORMULA] . " %";

    $this->setImgFile(Constants::LEGO_EV3_BRICK_IMG);
  }
}