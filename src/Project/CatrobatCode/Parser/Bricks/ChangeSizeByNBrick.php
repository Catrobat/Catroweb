<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class ChangeSizeByNBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::CHANGE_SIZE_BY_N_BRICK;
    $this->caption = 'Change size by _';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
