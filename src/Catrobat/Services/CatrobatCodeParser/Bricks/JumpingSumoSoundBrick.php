<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

//use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class JumpingSumoSoundBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class JumpingSumoSoundBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::JUMP_SUMO_SOUND_BRICK;
//        $formulas = FormulaResolver::resolve($this->brick_xml_properties->formulaList);

    $this->caption = "Play a sound.";

    $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
  }
}