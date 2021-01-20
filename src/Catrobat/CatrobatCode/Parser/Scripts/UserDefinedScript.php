<?php

namespace App\Catrobat\CatrobatCode\Parser\Scripts;

use App\Catrobat\CatrobatCode\Parser\Constants;

class UserDefinedScript extends Script
{
  protected function create(): void
  {
    $this->type = Constants::USER_DEFINED_SCRIPT;
    $this->caption = 'Unknown Script';
    $this->setImgFile(Constants::YOUR_SCRIPT_IMG);
  }
}
