<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class JumpingSumoRotateRightBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class JumpingSumoRotateRightBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::JUMP_SUMO_ROTATE_RIGHT_BRICK;
    $formulas = FormulaResolver::resolve($this->brick_xml_properties->formulaList);

    $this->caption = "ROTATE Sumo RIGHT by " . $formulas[Constants::JUMPING_SUMO_ROTATE] . " degrees";

    $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
  }
}