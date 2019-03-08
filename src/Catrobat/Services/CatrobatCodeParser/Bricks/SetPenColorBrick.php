<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class SetPenColorBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class SetPenColorBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::SET_PEN_COLOR_BRICK;
    $formulas = FormulaResolver::resolve($this->brick_xml_properties->formulaList);
    if (array_key_exists(Constants::PEN_COLOR_RED_FORMULA, $formulas) || array_key_exists(Constants::PEN_COLOR_BLUE_FORMULA, $formulas) ||
      array_key_exists(Constants::PEN_COLOR_GREEN_FORMULA, $formulas))
    {
      $this->caption = "Set pen color to Red: " . $formulas[Constants::PEN_COLOR_RED_FORMULA] . " Green: "
        . $formulas[Constants::PEN_COLOR_GREEN_FORMULA] . " Blue: " . $formulas[Constants::PEN_COLOR_BLUE_FORMULA];
    }
    else
    {
      $this->caption = "Set pen color to Red: " . $formulas[Constants::PEN_COLOR_RED_NEW_FORMULA] . " Green: "
        . $formulas[Constants::PEN_COLOR_GREEN_NEW_FORMULA] . " Blue: " . $formulas[Constants::PEN_COLOR_BLUE_NEW_FORMULA];
    }
    $this->setImgFile(Constants::PEN_BRICK_IMG);
  }
}