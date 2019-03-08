<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class ChangeXByNBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class ChangeXByNBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::CHANGE_X_BY_N_BRICK;
    $this->caption = "Change X by "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::X_POSITION_CHANGE_FORMULA];

    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}