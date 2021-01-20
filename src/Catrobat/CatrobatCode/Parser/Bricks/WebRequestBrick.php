<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class WebRequestBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::WEB_REQUEST_BRICK;
    $this->caption = 'Web Request';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
