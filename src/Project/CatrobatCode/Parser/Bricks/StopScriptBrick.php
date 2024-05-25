<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class StopScriptBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::STOP_SCRIPT_BRICK;
    $this->caption = 'Stop Script';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
