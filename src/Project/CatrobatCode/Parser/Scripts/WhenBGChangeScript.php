<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Scripts;

use App\Project\CatrobatCode\Parser\Constants;

class WhenBGChangeScript extends Script
{
  protected function create(): void
  {
    $this->type = Constants::WHEN_BG_CHANGE_SCRIPT;
    $this->caption = 'When background changes to _';
    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}
