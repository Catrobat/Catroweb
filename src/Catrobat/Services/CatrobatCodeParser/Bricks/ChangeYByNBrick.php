<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class ChangeYByNBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class ChangeYByNBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::CHANGE_Y_BY_N_BRICK;
    $this->caption = "Change Y by "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::Y_POSITION_CHANGE_FORMULA];

    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}