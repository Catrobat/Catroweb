<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class ReadListFromDeviceBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::READ_LIST_FROM_DEVICE_BRICK;
    $this->caption = 'Read list from device';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
