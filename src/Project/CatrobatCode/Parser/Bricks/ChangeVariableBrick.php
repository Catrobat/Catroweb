<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class ChangeVariableBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::CHANGE_VARIABLE_BRICK;
    $this->caption = 'Change variable _ by _';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
