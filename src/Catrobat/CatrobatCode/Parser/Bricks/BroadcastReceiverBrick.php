<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class BroadcastReceiverBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::BROADCAST_RECEIVER_BRICK;
    $this->caption = 'Broadcast receiver';
    $this->setImgFile(Constants::EVENT_BRICK_IMG);
  }
}
