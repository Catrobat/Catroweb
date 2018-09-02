<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

class AskSpeechBrick extends Brick
{
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