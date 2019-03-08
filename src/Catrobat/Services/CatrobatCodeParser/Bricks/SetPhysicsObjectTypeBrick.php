<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class SetPhysicsObjectTypeBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class SetPhysicsObjectTypeBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::SET_PHYSICS_OBJECT_TYPE_BRICK;
    $this->caption = "Set motion type to " . $this->brick_xml_properties->type;

    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}