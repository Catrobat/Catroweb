<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

class ComeToFrontBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::COME_TO_FRONT_BRICK;
    $this->caption = "Go to front";

    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}