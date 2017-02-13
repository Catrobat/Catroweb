<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

class LoopEndlessBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::LOOP_ENDLESS_BRICK;
        $this->caption = "End of loop";

        $this->setImgFile(Constants::CONTROL_BRICK_IMG);
    }
}