<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class FlashBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::FLASH_BRICK;
    $this->caption = 'Turn flashlight _';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
