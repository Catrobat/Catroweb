<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class BroadcastWaitBrick.
 */
class BroadcastWaitBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::BROADCAST_WAIT_BRICK;
    $this->caption = 'Broadcast and wait';
    $this->setImgFile(Constants::EVENT_BRICK_IMG);
  }
}
