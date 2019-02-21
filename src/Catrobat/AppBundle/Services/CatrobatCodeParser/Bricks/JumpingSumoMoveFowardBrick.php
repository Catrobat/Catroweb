<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class JumpingSumoMoveFowardBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
class JumpingSumoMoveFowardBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::JUMP_SUMO_MOVE_FOWARD_BRICK;
    $formulas = FormulaResolver::resolve($this->brick_xml_properties->formulaList);

    $this->caption = "MOVE Sumo FOWARD with " . $formulas[Constants::JUMP_SUMO_SPEED]
      . "% power for " . $formulas[Constants::JUMPING_SUMO_TIME_TO_DRIVE_IN_SECONDS] . " seconds";

    $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
  }
}
