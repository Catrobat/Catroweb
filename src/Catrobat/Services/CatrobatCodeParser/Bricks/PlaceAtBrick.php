<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class PlaceAtBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class PlaceAtBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::PLACE_AT_BRICK;

    $formulas = FormulaResolver::resolve($this->brick_xml_properties->formulaList);
    $this->caption = "Place at X: " . $formulas[Constants::X_POSITION_FORMULA]
      . " Y: " . $formulas[Constants::Y_POSITION_FORMULA];

    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}