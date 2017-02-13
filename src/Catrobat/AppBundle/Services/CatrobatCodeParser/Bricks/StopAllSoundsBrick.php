<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

class StopAllSoundsBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::STOP_ALL_SOUNDS_BRICK;
        $this->caption = "Stop all sounds";

        $this->setImgFile(Constants::SOUND_BRICK_IMG);
    }
}