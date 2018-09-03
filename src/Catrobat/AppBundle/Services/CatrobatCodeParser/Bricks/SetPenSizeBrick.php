<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

class SetPenSizeBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::SET_PEN_SIZE_BRICK;
    $this->caption = "Set pen size to "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::PEN_SIZE_FORMULA];

    $this->setImgFile(Constants::PEN_BRICK_IMG);
  }
}