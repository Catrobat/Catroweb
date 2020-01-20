<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class AssertEqualsBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class WebRequestBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::WEB_REQUEST_BRICK;
    $this->caption = "Web Request";
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}