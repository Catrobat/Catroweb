<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Scripts;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

class WhenScript extends Script
{
  protected function create()
  {
    $this->type = Constants::WHEN_SCRIPT;
    $this->caption = "When tapped";

    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}