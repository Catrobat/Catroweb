<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class IfBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class IfBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::IF_BRICK;
    $this->caption = "If "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::IF_CONDITION_FORMULA]
      . " is true then";

    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}