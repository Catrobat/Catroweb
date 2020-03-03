<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class AskBrick.
 */
class AskBrick extends Brick
{
  /**
   * @return mixed|void
   */
  protected function create()
  {
    $this->type = Constants::ASK_BRICK;
    $this->caption = 'Ask _ and store written answer in _';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
