<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class SetTransparencyBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class SetTransparencyBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::SET_TRANSPARENCY_BRICK;
    $this->caption = "Set transparency to "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::TRANSPARENCY_FORMULA] . "%";

    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}