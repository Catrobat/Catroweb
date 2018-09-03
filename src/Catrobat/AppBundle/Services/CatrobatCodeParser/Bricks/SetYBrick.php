<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

class SetYBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::SET_Y_BRICK;
    $this->caption = "Set Y to "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::Y_POSITION_FORMULA];

    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}