<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

/**
 * Class PenUpBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
class PenUpBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::PEN_UP_BRICK;
    $this->caption = "Pen up";

    $this->setImgFile(Constants::PEN_BRICK_IMG);
  }
}