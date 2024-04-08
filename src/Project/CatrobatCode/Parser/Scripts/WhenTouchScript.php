<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Scripts;

use App\Project\CatrobatCode\Parser\Constants;

class WhenTouchScript extends Script
{
  protected function create(): void
  {
    $this->type = Constants::WHEN_TOUCH_SCRIPT;
    $this->caption = 'When screen is touched';
    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}
