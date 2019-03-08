<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class GlideToBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class GlideToBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::GLIDE_TO_BRICK;

    $formulas = FormulaResolver::resolve($this->brick_xml_properties->formulaList);
    $this->caption = "Glide " . $formulas[Constants::DURATION_IN_SECONDS_FORMULA]
      . " second(s) to X: " . $formulas[Constants::X_DESTINATION_FORMULA]
      . " Y: " . $formulas[Constants::Y_DESTINATION_FORMUlA];

    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}