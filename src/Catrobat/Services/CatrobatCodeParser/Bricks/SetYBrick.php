<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class SetYBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class SetYBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::SET_Y_BRICK;
    $this->caption = "Set Y to "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::Y_POSITION_FORMULA];

    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}