<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

class ShowTextBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::SHOW_TEXT_BRICK;

        $variable = null;
        if ($this->brick_xml_properties->userVariable[Constants::REFERENCE_ATTRIBUTE] != null)
            $variable = (string)$this->brick_xml_properties->userVariable
              ->xpath($this->brick_xml_properties->userVariable[Constants::REFERENCE_ATTRIBUTE])[0];
        else
            $variable = (string)$this->brick_xml_properties->userVariable;
        $formulas = FormulaResolver::resolve($this->brick_xml_properties->formulaList);
        $this->caption = "Show variable " . $variable . " at X: "
          . $formulas[Constants::X_POSITION_FORMULA] . " Y: " . $formulas[Constants::Y_POSITION_FORMULA];

        $this->setImgFile(Constants::DATA_BRICK_IMG);
    }
}