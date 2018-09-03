<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

class BroadcastWaitBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::BROADCAST_WAIT_BRICK;
    $this->caption = "Broadcast and wait \"" . $this->brick_xml_properties->broadcastMessage . "\"";

    $this->setImgFile(Constants::EVENT_BRICK_IMG);
  }
}