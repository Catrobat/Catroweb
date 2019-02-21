<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class VibrationBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
class VibrationBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::VIBRATION_BRICK;
    $this->caption = "Vibrate for "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::VIBRATE_DURATION_IN_SECONDS_FORMULA]
      . " second(s)";

    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}