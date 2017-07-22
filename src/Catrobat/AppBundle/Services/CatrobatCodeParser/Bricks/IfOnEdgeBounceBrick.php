<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

class IfOnEdgeBounceBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::IF_ON_EDGE_BOUNCE_BRICK;
        $this->caption = "If on edge bounce";

        $this->setImgFile(Constants::MOTION_BRICK_IMG);
    }
}