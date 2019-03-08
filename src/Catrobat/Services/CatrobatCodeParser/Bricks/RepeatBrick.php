<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class RepeatBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class RepeatBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::REPEAT_BRICK;
    $this->caption = "Repeat "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::TIMES_TO_REPEAT_FORMULA]
      . " times";

    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}