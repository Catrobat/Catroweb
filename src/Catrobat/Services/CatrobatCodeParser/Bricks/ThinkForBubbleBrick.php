<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class ThinkForBubbleBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class ThinkForBubbleBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::THINK_FOR_BUBBLE_BRICK;
    $this->caption = "Think _ for _ seconds";
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}