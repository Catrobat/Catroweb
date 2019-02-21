<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class SpeakWaitBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
class SpeakWaitBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::SPEAK_WAIT_BRICK;
    $this->caption = "Speak \""
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::SPEAK_FORMULA] . "\" and wait";

    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}