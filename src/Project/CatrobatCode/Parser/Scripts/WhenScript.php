<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Scripts;

use App\Project\CatrobatCode\Parser\Constants;

class WhenScript extends Script
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::WHEN_SCRIPT;
    $this->caption = 'When tapped';
    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}
