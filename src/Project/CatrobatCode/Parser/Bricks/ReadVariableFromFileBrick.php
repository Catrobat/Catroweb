<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class ReadVariableFromFileBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::READ_VARIABLE_FROM_FILE_BRICK;
    $this->caption = 'Read Variable From File Brick';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
