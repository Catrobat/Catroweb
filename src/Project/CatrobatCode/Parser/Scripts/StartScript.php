<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Scripts;

use App\Project\CatrobatCode\Parser\Constants;

class StartScript extends Script
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::START_SCRIPT;
    $this->caption = 'When program started';
    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}
