<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class ContinueSceneBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::CONTINUE_SCENE_BRICK;
    $this->caption = 'Continue scene _';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
