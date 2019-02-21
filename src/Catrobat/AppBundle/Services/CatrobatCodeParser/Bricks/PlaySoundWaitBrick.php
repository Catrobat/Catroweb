<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

/**
 * Class PlaySoundWaitBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
class PlaySoundWaitBrick extends Brick
{
  /**
   * @var
   */
  private $sound_file_name;

  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::PLAY_SOUND_WAIT_BRICK;
    $this->caption = "Start sound and wait";

    if ($this->brick_xml_properties->sound[Constants::REFERENCE_ATTRIBUTE] != null)
    {
      $this->sound_file_name = $this->brick_xml_properties->sound
        ->xpath($this->brick_xml_properties->sound[Constants::REFERENCE_ATTRIBUTE])[0]->fileName;
    }
    else
    {
      $this->sound_file_name = $this->brick_xml_properties->sound->fileName;
    }

    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }

  /**
   * @return mixed
   */
  public function getSoundFileName()
  {
    return $this->sound_file_name;
  }
}