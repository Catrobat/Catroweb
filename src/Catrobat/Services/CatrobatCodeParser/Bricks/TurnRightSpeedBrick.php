<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class TurnRightSpeedBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class TurnRightSpeedBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::TURN_RIGHT_SPEED_BRICK;
    $this->caption = "Rotate right "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::TURN_RIGHT_SPEED_FORMULA]
      . " degrees/second";

    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}