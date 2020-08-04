<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class DeleteThisCloneBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::DELETE_THIS_CLONE_BRICK;
    $this->caption = 'Delete this';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
