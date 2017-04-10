<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

class SetFrictionBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::SET_FRICTION_BRICK;
        $this->caption = "Set friction to "
          . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::FRICTION_FORMULA] . "%";

        $this->setImgFile(Constants::MOTION_BRICK_IMG);
    }
}