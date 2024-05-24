<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class WriteVariableToFileBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::WRITE_VARIABLE_TO_FILE_BRICK;
    $this->caption = 'Write Variable To File Brick';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
