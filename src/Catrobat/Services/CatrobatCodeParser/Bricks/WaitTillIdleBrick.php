<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class WaitTillIdleBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class WaitTillIdleBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::WAIT_TILL_IDLE_BRICK;
    $this->caption = "Wait till idle";
    $this->setImgFile(Constants::TESTING_BRICK_IMG);
  }
}