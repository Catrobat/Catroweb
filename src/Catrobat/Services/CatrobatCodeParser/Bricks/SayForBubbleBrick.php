<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class SayForBubbleBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class SayForBubbleBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::SAY_FOR_BUBBLE_BRICK;

    $formulas = FormulaResolver::resolve($this->brick_xml_properties->formulaList);
    $this->caption = "Say \"" . $formulas[Constants::STRING_FORMULA] . "\" for "
      . $formulas[Constants::DURATION_IN_SECONDS_FORMULA] . " second/s";

    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}