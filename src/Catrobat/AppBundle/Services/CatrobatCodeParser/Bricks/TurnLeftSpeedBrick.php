<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class TurnLeftSpeedBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
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