<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Scripts;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

class BroadcastScript extends Script
{
  protected function create()
  {
    $this->type = Constants::BROADCAST_SCRIPT;
    $this->caption = "When I receive \"" . $this->script_xml_properties->receivedMessage . "\"";

    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}