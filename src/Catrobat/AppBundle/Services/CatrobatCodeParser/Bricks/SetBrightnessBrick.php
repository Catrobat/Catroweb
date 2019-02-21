<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class SetBrightnessBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
class SetBrightnessBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::SET_BRIGHTNESS_BRICK;
    $this->caption = "Set brightness to "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::BRIGHTNESS_FORMULA] . "%";

    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}