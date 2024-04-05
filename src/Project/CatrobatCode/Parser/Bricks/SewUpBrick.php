<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class SewUpBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SEW_UP_BRICK;
    $this->caption = 'Sew up';
    $this->setImgFile(Constants::EMBROIDERY_BRICK_IMG);
  }
}
