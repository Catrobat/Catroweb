<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class BroadcastBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::BROADCAST_BRICK;
    $this->caption = 'Broadcast';
    $this->setImgFile(Constants::EVENT_BRICK_IMG);
  }
}
