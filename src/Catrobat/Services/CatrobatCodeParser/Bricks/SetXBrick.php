<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class SetXBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class SetXBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::SET_X_BRICK;
    $this->caption = "Set X to "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::X_POSITION_FORMULA];

    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}