<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

class ElseBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::ELSE_BRICK;
        $this->caption = "Else";

        $this->setImgFile(Constants::CONTROL_BRICK_IMG);
    }
}