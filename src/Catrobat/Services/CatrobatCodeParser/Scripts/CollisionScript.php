<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Scripts;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class CollisionScript
 * @package App\Catrobat\Services\CatrobatCodeParser\Scripts
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