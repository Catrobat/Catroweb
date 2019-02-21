<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Scripts;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

/**
 * Class CollisionScript
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Scripts
 */
class CollisionScript extends Script
{
  /**
   * @return mixed|void
   */
  protected function create()
  {
    $this->type = Constants::COLLISION_SCRIPT;
    $this->caption = "When physical collision with \"" . $this->script_xml_properties->receivedMessage . "\"";

    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}