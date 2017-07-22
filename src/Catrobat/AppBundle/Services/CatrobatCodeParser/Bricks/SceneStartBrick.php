<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

class SceneStartBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::SCENE_START_BRICK;
        $this->caption = "Start scene " . $this->brick_xml_properties->sceneToStart;

        $this->setImgFile(Constants::CONTROL_BRICK_IMG);
    }
}