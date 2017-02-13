<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

class SetPenColorBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::SET_PEN_COLOR_BRICK;

        $formulas = FormulaResolver::resolve($this->brick_xml_properties->formulaList);
        $this->caption = "Set pen color to Red: " . $formulas[Constants::PEN_COLOR_RED_FORMULA] . " Green: "
          . $formulas[Constants::PEN_COLOR_GREEN_FORMULA] . " Blue: " . $formulas[Constants::PEN_COLOR_BLUE_FORMULA];

        $this->setImgFile(Constants::PEN_BRICK_IMG);
    }
}