<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

/**
 * Class NextLookBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
class NextLookBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::NEXT_LOOK_BRICK;
    $this->caption = "Next look";

    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}