<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

class MoveNStepsBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::MOVE_N_STEPS_BRICK;
        $this->caption = "Move "
          . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::STEPS_FORMUlA] . " steps";

        $this->setImgFile(Constants::MOTION_BRICK_IMG);
    }
}