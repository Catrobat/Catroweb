<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class NoteBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::NOTE_BRICK;
    $this->caption = 'Note _';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
