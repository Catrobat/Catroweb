<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Scripts;

use App\Project\CatrobatCode\Parser\Constants;

class WhenBounceOffScript extends Script
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::WHEN_BOUNCE_OFF_SCRIPT;
    $this->caption = 'When you bounce off';
    $this->setImgFile(Constants::MOTION_SCRIPT_IMG);
  }
}
