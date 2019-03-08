<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class ChangeColorByNBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class ChangeColorByNBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::CHANGE_COLOR_BY_N_BRICK;
    $this->caption = "Change color by "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::COLOR_CHANGE_FORMULA];

    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}