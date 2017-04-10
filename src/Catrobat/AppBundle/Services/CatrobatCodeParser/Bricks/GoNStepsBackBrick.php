<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

class GoNStepsBackBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::GO_N_STEPS_BACK_BRICK;
        $this->caption = "Go back "
          . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::STEPS_FORMUlA] . " layer";

        $this->setImgFile(Constants::MOTION_BRICK_IMG);
    }
}