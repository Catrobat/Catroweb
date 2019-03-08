<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class LoopEndBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class LoopEndBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::LOOP_END_BRICK;
    $this->caption = "End of loop";

    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}