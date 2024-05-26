<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class SceneStartBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::SCENE_START_BRICK;
    $this->caption = 'Start scene _';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
