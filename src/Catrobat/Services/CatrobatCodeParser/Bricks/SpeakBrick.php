<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class SpeakBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
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