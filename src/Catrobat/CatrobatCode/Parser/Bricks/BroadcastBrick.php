<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class BroadcastBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::BROADCAST_BRICK;
    $this->caption = 'Broadcast';
    $this->setImgFile(Constants::EVENT_BRICK_IMG);
  }
}
