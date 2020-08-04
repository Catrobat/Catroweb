<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class IfOnEdgeBounceBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::IF_ON_EDGE_BOUNCE_BRICK;
    $this->caption = 'If on edge bounce';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
