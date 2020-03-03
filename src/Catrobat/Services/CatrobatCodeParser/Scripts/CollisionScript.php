<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Scripts;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class CollisionScript.
 */
class CollisionScript extends Script
{
  /**
   * @return mixed|void
   */
  protected function create()
  {
    $this->type = Constants::COLLISION_SCRIPT;
    $this->caption = 'Collision Script (deprecated)';
    $this->setImgFile(Constants::DEPRECATED_SCRIPT_IMG);
  }
}
