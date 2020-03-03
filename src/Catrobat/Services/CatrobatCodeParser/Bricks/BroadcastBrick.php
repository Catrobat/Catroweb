<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class BroadcastBrick.
 */
class BroadcastBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::BROADCAST_BRICK;
    $this->caption = 'Broadcast';
    $this->setImgFile(Constants::EVENT_BRICK_IMG);
  }
}
