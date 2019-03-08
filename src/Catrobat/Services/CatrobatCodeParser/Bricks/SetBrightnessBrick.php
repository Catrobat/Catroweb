<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class SetBrightnessBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
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