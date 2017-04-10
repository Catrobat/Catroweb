<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

class SetBackgroundBrick extends Brick
{
    private $look_file_name;

    protected function create()
    {
        $this->type = Constants::SET_BACKGROUND_BRICK;
        $this->caption = "Set background";

        if ($this->brick_xml_properties->look[Constants::REFERENCE_ATTRIBUTE] != null)
            $this->look_file_name = $this->brick_xml_properties->look
              ->xpath($this->brick_xml_properties->look[Constants::REFERENCE_ATTRIBUTE])[0]->fileName;
        else
            $this->look_file_name = $this->brick_xml_properties->look->fileName;

        $this->setImgFile(Constants::LOOKS_BRICK_IMG);
    }

    public function getLookFileName()
    {
        return $this->look_file_name;
    }
}