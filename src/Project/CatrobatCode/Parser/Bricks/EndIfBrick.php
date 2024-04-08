<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class EndIfBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::ENDIF_BRICK;
    $this->caption = 'End If';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
