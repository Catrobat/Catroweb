<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class SetGravityBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class SetGravityBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::SET_GRAVITY_BRICK;

    $formulas = FormulaResolver::resolve($this->brick_xml_properties->formulaList);
    $this->caption = "Set gravity for all objects to X: "
      . $formulas[Constants::GRAVITY_X_FORMULA] . " Y: "
      . $formulas[Constants::GRAVITY_Y_FORMULA] . " steps/second²";

    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}