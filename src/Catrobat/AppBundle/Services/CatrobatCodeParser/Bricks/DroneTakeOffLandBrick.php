<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
class DroneTakeOffLandBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::AR_DRONE_TAKE_OFF_LAND_BRICK;
        $this->caption = "Take off or land drone";

        $this->setImgFile(Constants::AR_DRONE_BRICK_IMG);
    }
}