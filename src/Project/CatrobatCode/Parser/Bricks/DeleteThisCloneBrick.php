<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class DeleteThisCloneBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::DELETE_THIS_CLONE_BRICK;
    $this->caption = 'Delete this';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
