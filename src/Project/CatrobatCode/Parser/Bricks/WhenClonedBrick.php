<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;
use App\Project\CatrobatCode\Parser\Scripts\Script;

class WhenClonedBrick extends Script
{
  protected function create(): void
  {
    $this->type = Constants::WHEN_CLONED_BRICK;
    $this->caption = 'When I start as a clone';
    $this->setImgFile(Constants::CONTROL_SCRIPT_IMG);
  }
}
