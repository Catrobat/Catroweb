<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

class BroadcastBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::BROADCAST_BRICK;
        $this->caption = "Broadcast \"" . $this->brick_xml_properties->broadcastMessage . "\"";

        $this->setImgFile(Constants::EVENT_BRICK_IMG);
    }
}