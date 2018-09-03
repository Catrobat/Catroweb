<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

class IfBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::IF_BRICK;
    $this->caption = "If "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::IF_CONDITION_FORMULA]
      . " is true then";

    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}