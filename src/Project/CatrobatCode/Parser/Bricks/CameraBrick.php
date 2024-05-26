<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class CameraBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::CAMERA_BRICK;
    $this->caption = 'Turn camera _';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
