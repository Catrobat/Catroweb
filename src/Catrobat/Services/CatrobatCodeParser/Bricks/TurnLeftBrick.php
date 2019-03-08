<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class TurnLeftBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class TurnLeftBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::TURN_LEFT_BRICK;
    $this->caption = "Turn left "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::TURN_LEFT_DEGREES_FORMULA]
      . " degrees";

    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}