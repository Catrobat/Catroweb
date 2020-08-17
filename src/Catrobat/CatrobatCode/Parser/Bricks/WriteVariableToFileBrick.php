<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class WriteVariableToFileBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::WRITE_VARIABLE_TO_FILE_BRICK;
    $this->caption = 'Write Variable To File Brick';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
