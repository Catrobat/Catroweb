<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

class PlaceAtBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::PLACE_AT_BRICK;

        $formulas = FormulaResolver::resolve($this->brick_xml_properties->formulaList);
        $this->caption = "Place at X: " . $formulas[Constants::X_POSITION_FORMULA]
          . " Y: " . $formulas[Constants::Y_POSITION_FORMULA];

        $this->setImgFile(Constants::MOTION_BRICK_IMG);
    }
}