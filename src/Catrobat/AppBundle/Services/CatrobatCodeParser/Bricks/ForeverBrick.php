<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

class ForeverBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::FOREVER_BRICK;
        $this->caption = "Forever";

        $this->setImgFile(Constants::CONTROL_BRICK_IMG);
    }
}