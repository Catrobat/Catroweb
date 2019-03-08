<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class RepeatUntilBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class RepeatUntilBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::REPEAT_UNTIL_BRICK;
    $this->caption = "Repeat until "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::REPEAT_UNTIL_CONDITION_FORMULA]
      . " is true";

    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}