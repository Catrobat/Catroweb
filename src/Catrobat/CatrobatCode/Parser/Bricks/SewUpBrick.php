<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class SewUpBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SEW_UP_BRICK;
    $this->caption = 'Sew up';
    $this->setImgFile(Constants::EMBROIDERY_BRICK_IMG);
  }
}
