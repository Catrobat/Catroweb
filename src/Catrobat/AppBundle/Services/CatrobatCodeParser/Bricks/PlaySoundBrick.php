<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

class PlaySoundBrick extends Brick
{
    private $sound_file_name;

    protected function create()
    {
        $this->type = Constants::PLAY_SOUND_BRICK;
        $this->caption = "Start sound";
        
        if ($this->brick_xml_properties->sound[Constants::REFERENCE_ATTRIBUTE != null])
            $this->sound_file_name = $this->brick_xml_properties->sound
              ->xpath($this->brick_xml_properties->sound[Constants::REFERENCE_ATTRIBUTE])[0]->fileName;
        else
            $this->sound_file_name = $this->brick_xml_properties->sound->fileName;

        $this->setImgFile(Constants::SOUND_BRICK_IMG);
    }

    public function getSoundFileName()
    {
        return $this->sound_file_name;
    }
}