<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

class UnknownBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::UNKNOWN_BRICK;
        $this->caption = "Unknown Brick";

        $this->setImgFile(Constants::UNKNOWN_BRICK_IMG);
    }
}