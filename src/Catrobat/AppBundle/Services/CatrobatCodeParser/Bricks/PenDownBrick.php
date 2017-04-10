<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

class PenDownBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::PEN_DOWN_BRICK;
        $this->caption = "Pen down";

        $this->setImgFile(Constants::PEN_BRICK_IMG);
    }
}