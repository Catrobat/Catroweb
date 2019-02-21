<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class SetXBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
class SetXBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::SET_X_BRICK;
    $this->caption = "Set X to "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::X_POSITION_FORMULA];

    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}