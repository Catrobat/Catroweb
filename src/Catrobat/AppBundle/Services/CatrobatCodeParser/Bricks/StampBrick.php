<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

/**
 * Class StampBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
class StampBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::STAMP_BRICK;
    $this->caption = "Stamp";

    $this->setImgFile(Constants::PEN_BRICK_IMG);
  }
}