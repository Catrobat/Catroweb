<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class BroadcastWaitBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::BROADCAST_WAIT_BRICK;
    $this->caption = 'Broadcast and wait';
    $this->setImgFile(Constants::EVENT_BRICK_IMG);
  }
}
