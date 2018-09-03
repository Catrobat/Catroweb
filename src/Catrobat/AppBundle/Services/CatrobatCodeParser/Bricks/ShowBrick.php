<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

class ShowBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::SHOW_BRICK;
    $this->caption = "Show";

    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}