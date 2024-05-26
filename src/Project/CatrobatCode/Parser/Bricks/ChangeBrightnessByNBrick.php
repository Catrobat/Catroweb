<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class ChangeBrightnessByNBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::CHANGE_BRIGHTNESS_BY_N_BRICK;
    $this->caption = 'Change brightness by _';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
