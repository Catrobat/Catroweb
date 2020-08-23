<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class ReadVariableFromFileBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::READ_VARIABLE_FROM_FILE_BRICK;
    $this->caption = 'Read Variable From File Brick';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
