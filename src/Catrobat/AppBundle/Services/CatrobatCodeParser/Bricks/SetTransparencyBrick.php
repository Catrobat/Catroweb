<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

class SetTransparencyBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::SET_TRANSPARENCY_BRICK;
        $this->caption = "Set transparency to "
          . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::TRANSPARENCY_FORMULA] . "%";

        $this->setImgFile(Constants::LOOKS_BRICK_IMG);
    }
}