<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

//use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

class JumpingSumoSoundBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::JUMP_SUMO_SOUND_BRICK;
//        $formulas = FormulaResolver::resolve($this->brick_xml_properties->formulaList);

    $this->caption = "Play a sound.";

    $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
  }
}