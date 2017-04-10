<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

class ChangeXByNBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::CHANGE_X_BY_N_BRICK;
        $this->caption = "Change X by "
          . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::X_POSITION_CHANGE_FORMULA];

        $this->setImgFile(Constants::MOTION_BRICK_IMG);
    }
}