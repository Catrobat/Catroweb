<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class PhiroIfLogicBeginBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::PHIRO_IF_LOGIC_BEGIN_BRICK;
    $this->caption = 'If Phiro _ is activated';
    $this->setImgFile(Constants::PHIRO_CONTROL_BRICK_IMG);
  }
}
