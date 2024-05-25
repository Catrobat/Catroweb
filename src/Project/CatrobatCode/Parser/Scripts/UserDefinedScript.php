<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Scripts;

use App\Project\CatrobatCode\Parser\Constants;

class UserDefinedScript extends Script
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::USER_DEFINED_SCRIPT;
    $this->caption = 'Unknown Script';
    $this->setImgFile(Constants::YOUR_SCRIPT_IMG);
  }
}
