<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

/**
 * Class HideTextBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
class HideTextBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::HIDE_TEXT_BRICK;

    $variable = null;
    if ($this->brick_xml_properties->userVariable[Constants::REFERENCE_ATTRIBUTE] != null)
    {
      $variable = (string)$this->brick_xml_properties->userVariable
        ->xpath($this->brick_xml_properties->userVariable[Constants::REFERENCE_ATTRIBUTE])[0];
    }
    else
    {
      $variable = (string)$this->brick_xml_properties->userVariable;
    }
    $this->caption = "Hide variable " . $variable;

    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}