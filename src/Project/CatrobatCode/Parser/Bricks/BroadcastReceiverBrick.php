<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class BroadcastReceiverBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::BROADCAST_RECEIVER_BRICK;
    $this->caption = 'Broadcast receiver';
    $this->setImgFile(Constants::EVENT_BRICK_IMG);
  }
}
