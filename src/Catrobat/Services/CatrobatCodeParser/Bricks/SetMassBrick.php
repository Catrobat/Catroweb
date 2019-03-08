<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class SetMassBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class SetMassBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::SET_MASS_BRICK;
    $this->caption = "Set mass to "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::MASS_FORMULA] . " kilogram";

    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}