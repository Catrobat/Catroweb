<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

class NoteBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::NOTE_BRICK;
        $this->caption = "Note "
          . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::NOTE_FORMULA];

        $this->setImgFile(Constants::CONTROL_BRICK_IMG);
    }
}