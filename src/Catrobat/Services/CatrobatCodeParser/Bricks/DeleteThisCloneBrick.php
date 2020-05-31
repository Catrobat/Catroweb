<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class DeleteThisCloneBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::DELETE_THIS_CLONE_BRICK;
    $this->caption = 'Delete this';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
