<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

/**
 * Class HideBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
class HideBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::HIDE_BRICK;
    $this->caption = "Hide";

    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}