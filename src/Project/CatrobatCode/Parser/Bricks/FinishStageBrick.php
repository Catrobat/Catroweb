<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class FinishStageBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::FINISH_STAGE_BRICK;
    $this->caption = 'Finish Stage Brick';
    $this->setImgFile(Constants::UNKNOWN_BRICK_IMG);
  }
}
