<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class BroadcastReceiverBrick.
 */
class BroadcastReceiverBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::BROADCAST_RECEIVER_BRICK;
    $this->caption = 'Broadcast receiver';
    $this->setImgFile(Constants::EVENT_BRICK_IMG);
  }
}
