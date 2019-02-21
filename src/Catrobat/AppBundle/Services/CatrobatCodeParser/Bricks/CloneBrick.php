<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

/**
 * Class CloneBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
class CloneBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::CLONE_BRICK;

    $clone_name = null;
    if (count($this->brick_xml_properties->objectToClone) != 0)
    {
      $clone_name = $this->brick_xml_properties->objectToClone->xpath($this->brick_xml_properties
        ->objectToClone[Constants::REFERENCE_ATTRIBUTE])[0][Constants::NAME_ATTRIBUTE];
    }
    else
    {
      $clone_name = "myself";
    }
    $this->caption = "Create clone of " . $clone_name;

    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}