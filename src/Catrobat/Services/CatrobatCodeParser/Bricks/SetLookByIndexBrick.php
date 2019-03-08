<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class SetLookByIndexBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class SetLookByIndexBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::SET_LOOK_BY_INDEX_BRICK;
    $this->caption = "Switch to look number " . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::LOOK_INDEX];

    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}