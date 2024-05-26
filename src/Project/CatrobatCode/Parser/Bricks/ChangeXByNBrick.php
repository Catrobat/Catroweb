<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class ChangeXByNBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::CHANGE_X_BY_N_BRICK;
    $this->caption = 'Change X by _';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
