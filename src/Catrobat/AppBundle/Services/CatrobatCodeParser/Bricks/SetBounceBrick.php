<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

class SetBounceBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::SET_BOUNCE_BRICK;
    $this->caption = "Set bounce factor to "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::BOUNCE_FACTOR_FORMULA] . "%";

    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}