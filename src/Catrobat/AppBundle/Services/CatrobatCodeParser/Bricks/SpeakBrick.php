<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class SpeakBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
class SpeakBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::SPEAK_BRICK;
    $this->caption = "Speak \""
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::SPEAK_FORMULA] . "\"";

    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}