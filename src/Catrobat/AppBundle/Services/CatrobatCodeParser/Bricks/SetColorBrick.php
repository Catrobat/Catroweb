<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

class SetColorBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::SET_COLOR_BRICK;
        $this->caption = "Set color to "
          . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::COLOR_FORMUlA] . "%";

        $this->setImgFile(Constants::LOOKS_BRICK_IMG);
    }
}