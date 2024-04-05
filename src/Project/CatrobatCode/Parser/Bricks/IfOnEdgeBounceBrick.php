<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class IfOnEdgeBounceBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::IF_ON_EDGE_BOUNCE_BRICK;
    $this->caption = 'If on edge bounce';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
