<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

class TurnRightBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::TURN_RIGHT_BRICK;
    $this->caption = "Turn right "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::TURN_RIGHT_DEGREES_FORMULA]
      . " degrees";

    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}