<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;
use App\Project\CatrobatCode\Parser\Scripts\Script;

class WhenBGChangeBrick extends Script
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::WHEN_BG_CHANGE_BRICK;
    $this->caption = 'When background changes to _';
    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}
