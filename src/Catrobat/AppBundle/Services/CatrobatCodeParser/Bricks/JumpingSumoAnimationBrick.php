<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;


/**
 * Class JumpingSumoAnimationBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
class JumpingSumoAnimationBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::JUMP_SUMO_ANIMATIONS_BRICK;
//        $formulas = FormulaResolver::resolve($this->brick_xml_properties->formulaList);
//
//        $this->caption = "MOVE Sumo BACKWARD with " . $formulas[Constants::JUMP_SUMO_SPEED]
//            . "% power for " . $formulas[Constants::JUMPING_SUMO_TIME_TO_DRIVE_IN_SECONDS] . " seconds";

    $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
  }
}