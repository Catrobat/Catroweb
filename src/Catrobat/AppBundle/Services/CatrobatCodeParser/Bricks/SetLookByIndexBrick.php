<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

class SetLookByIndexBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::SET_LOOK_BY_INDEX_BRICK;
        $this->caption = "Switch to look number " . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::LOOK_INDEX];

      $this->setImgFile(Constants::LOOKS_BRICK_IMG);
    }
}