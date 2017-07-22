<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

class WaitBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::WAIT_BRICK;
        $this->caption = "Wait "
          . FormulaResolver::resolve($this->brick_xml_properties->formulaList)
          [Constants::TIME_TO_WAIT_IN_SECONDS_FORMULA] . " second(s)";

        $this->setImgFile(Constants::CONTROL_BRICK_IMG);
    }
}