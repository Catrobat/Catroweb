<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class TurnRightSpeedBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
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