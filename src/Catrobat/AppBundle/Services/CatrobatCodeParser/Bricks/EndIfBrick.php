<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

class EndIfBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::ENDIF_BRICK;
        $this->caption = "End If";

        $this->setImgFile(Constants::CONTROL_BRICK_IMG);
    }
}