<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

class PrevLookBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::PREV_LOOK_BRICK;
        $this->caption = "Previous look";

        $this->setImgFile(Constants::LOOKS_BRICK_IMG);
    }
}