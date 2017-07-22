<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

class SetMassBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::SET_MASS_BRICK;
        $this->caption = "Set mass to "
          . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::MASS_FORMULA] . " kilogram";

        $this->setImgFile(Constants::MOTION_BRICK_IMG);
    }
}