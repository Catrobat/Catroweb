<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class JumpingSumoMoveBackwardBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class JumpingSumoMoveBackwardBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::JUMP_SUMO_MOVE_FOWARD_BRICK;
    $formulas = FormulaResolver::resolve($this->brick_xml_properties->formulaList);

    $this->caption = "MOVE Sumo BACKWARD with " . $formulas[Constants::JUMP_SUMO_SPEED]
      . "% power for " . $formulas[Constants::JUMPING_SUMO_TIME_TO_DRIVE_IN_SECONDS] . " seconds";

    $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
  }
}