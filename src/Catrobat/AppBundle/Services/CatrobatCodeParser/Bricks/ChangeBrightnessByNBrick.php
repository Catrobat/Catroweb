<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

class ChangeBrightnessByNBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::CHANGE_BRIGHTNESS_BY_N_BRICK;
    $this->caption = "Change brightness by "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::BRIGHTNESS_CHANGE_FORMULA];

    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}