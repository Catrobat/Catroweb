<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class NoteBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::NOTE_BRICK;
    $this->caption = 'Note _';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
