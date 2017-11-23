<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
class JumpingSumoTakingPictureBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::JUMP_SUMO_TAKING_PICTURE_BRICK;
        $this->caption = "Take a picture";

        $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
    }
}