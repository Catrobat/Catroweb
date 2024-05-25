<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class ElseBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::ELSE_BRICK;
    $this->caption = 'Else';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
