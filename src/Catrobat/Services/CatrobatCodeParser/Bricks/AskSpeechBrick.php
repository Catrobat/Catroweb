<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class AskSpeechBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class AskSpeechBrick extends Brick
{
  /**
   * @return mixed|void
   */
  protected function create()
  {
    $this->type = Constants::ASK_SPEECH_BRICK;

    $variable = null;
    if ($this->brick_xml_properties->userVariable[Constants::REFERENCE_ATTRIBUTE] != null)
    {
      $variable =
        $this->brick_xml_properties->userVariable
          ->xpath($this->brick_xml_properties->userVariable[Constants::REFERENCE_ATTRIBUTE])[0];
    }
    else
    {
      $variable = $this->brick_xml_properties->userVariable;
    }
    $this->caption = "Ask \""
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::ASK_SPEECH_QUESTION_FORMULA]
      . "\" and store written answer in " . $variable;

    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}