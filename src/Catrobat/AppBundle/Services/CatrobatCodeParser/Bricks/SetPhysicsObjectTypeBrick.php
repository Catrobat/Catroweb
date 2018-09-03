<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

class SetPhysicsObjectTypeBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::SET_PHYSICS_OBJECT_TYPE_BRICK;
    $this->caption = "Set motion type to " . $this->brick_xml_properties->type;

    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}