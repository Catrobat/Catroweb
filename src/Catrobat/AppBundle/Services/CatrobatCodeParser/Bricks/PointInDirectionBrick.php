<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

class PointInDirectionBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::POINT_IN_DIRECTION_BRICK;
        $this->caption = "Point in direction "
          . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::DEGREES_FORMULA] . " degrees";

        $this->setImgFile(Constants::MOTION_BRICK_IMG);
    }
}