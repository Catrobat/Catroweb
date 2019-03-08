<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class SetPenSizeBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class SetPenSizeBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::SET_PEN_SIZE_BRICK;
    $this->caption = "Set pen size to "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::PEN_SIZE_FORMULA];

    $this->setImgFile(Constants::PEN_BRICK_IMG);
  }
}