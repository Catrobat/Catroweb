<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

class SetGravityBrick extends Brick
{
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