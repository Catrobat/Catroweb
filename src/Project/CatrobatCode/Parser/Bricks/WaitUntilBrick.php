<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class WaitUntilBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::WAIT_UNTIL_BRICK;
    $this->caption = 'Wait until _ is true';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
