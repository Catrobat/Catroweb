<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class WriteEmbroideryToFileBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::WRITE_EMBROIDERY_TO_FILE_BRICK;
    $this->caption = 'Write embroidery data to file _';
    $this->setImgFile(Constants::EMBROIDERY_BRICK_IMG);
  }
}
