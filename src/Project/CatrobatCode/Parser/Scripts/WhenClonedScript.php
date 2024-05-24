<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Scripts;

use App\Project\CatrobatCode\Parser\Constants;

class WhenClonedScript extends Script
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::WHEN_CLONED_SCRIPT;
    $this->caption = 'When I start as a clone';
    $this->setImgFile(Constants::CONTROL_SCRIPT_IMG);
  }
}
