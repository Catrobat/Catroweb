<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class JumpingSumoRotateLeftBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
class JumpingSumoRotateLeftBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::JUMP_SUMO_ROTATE_LEFT_BRICK;
    $formulas = FormulaResolver::resolve($this->brick_xml_properties->formulaList);

    $this->caption = "ROTATE Sumo LEFT by " . $formulas[Constants::JUMPING_SUMO_ROTATE] . " degrees";

    $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
  }
}