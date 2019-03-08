<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class PointToBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::POINT_TO_BRICK;

    $pointed_object_name = null;
    if ($this->brick_xml_properties->pointedObject[Constants::REFERENCE_ATTRIBUTE != null])
    {
      $pointed_object_name =
        (string)$this->brick_xml_properties->pointedObject
          ->xpath($this->brick_xml_properties->pointedObject[Constants::REFERENCE_ATTRIBUTE])[0][Constants::NAME_ATTRIBUTE];
    }
    else
    {
      $pointed_object_name = (string)$this->brick_xml_properties->pointedObject[Constants::NAME_ATTRIBUTE];
    }
    $this->caption = "Point towards " . $pointed_object_name;

    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}