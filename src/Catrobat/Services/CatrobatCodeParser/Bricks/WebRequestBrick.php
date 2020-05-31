<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class WebRequestBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::WEB_REQUEST_BRICK;
    $this->caption = 'Web Request';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
