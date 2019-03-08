<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class SetBounceBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class SetBounceBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::SET_BOUNCE_BRICK;
    $this->caption = "Set bounce factor to "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::BOUNCE_FACTOR_FORMULA] . "%";

    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}