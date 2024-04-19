<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class EditLookBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::EDIT_LOOK_BRICK;
    $this->caption = 'Edit look';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
