<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class NoteBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::NOTE_BRICK;
    $this->caption = 'Note _';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
