<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
class DroneSwitchCameraBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::AR_DRONE_SWITCH_CAMERA_BRICK;
        $this->caption = "Switch Camera";

        $this->setImgFile(Constants::AR_DRONE_BRICK_IMG);
    }
}