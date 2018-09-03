<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

class WhenBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::WHEN_BRICK;
    $this->caption = "When tapped";

    $this->setImgFile(Constants::EVENT_BRICK_IMG);
  }
}