<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class WriteListOnDeviceBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::WRITE_LIST_ON_DEVICE_BRICK;
    $this->caption = 'Write list on device';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
