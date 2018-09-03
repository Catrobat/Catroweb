<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

class ClearBackgroundBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::CLEAR_BACKGROUND_BRICK;
    $this->caption = "Clear";

    $this->setImgFile(Constants::PEN_BRICK_IMG);
  }
}