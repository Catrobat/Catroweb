<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class WriteVariableOnDeviceBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::WRITE_VARIABLE_ON_DEVICE_BRICK;
    $this->caption = 'Write variable on device _';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
