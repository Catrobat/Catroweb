<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class SetVelocityBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
class SetVelocityBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::SET_VELOCITY_BRICK;

    $formulas = FormulaResolver::resolve($this->brick_xml_properties->formulaList);
    $this->caption = "Set velocity to X: " . $formulas[Constants::VELOCITY_X_FORMULA] . " Y: "
      . $formulas[Constants::VELOCITY_Y_FORMULA] . " steps/second";

    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}