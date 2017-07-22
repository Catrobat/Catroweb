<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

class DeleteThisCloneBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::DELETE_THIS_CLONE_BRICK;
        $this->caption = "Delete this";

        $this->setImgFile(Constants::CONTROL_BRICK_IMG);
    }
}