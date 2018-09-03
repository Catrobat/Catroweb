<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Scripts;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

class WhenClonedScript extends Script
{
  protected function create()
  {
    $this->type = Constants::WHEN_CLONED_SCRIPT;
    $this->caption = "When I start as a clone";

    $this->setImgFile(Constants::CONTROL_SCRIPT_IMG);
  }
}