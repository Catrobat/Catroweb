<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class TurnLeftSpeedBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class TurnLeftSpeedBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::TURN_LEFT_SPEED_BRICK;
    $this->caption = "Rotate left "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::TURN_LEFT_SPEED_FORMULA]
      . " degrees/second";

    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}