<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

class TurnLeftBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::TURN_LEFT_BRICK;
        $this->caption = "Turn left "
          . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::TURN_LEFT_DEGREES_FORMULA]
          . " degrees";

        $this->setImgFile(Constants::MOTION_BRICK_IMG);
    }
}