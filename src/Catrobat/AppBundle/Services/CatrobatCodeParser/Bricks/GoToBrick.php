<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

class GoToBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::GO_TO_BRICK;

        $destination = null;
        switch((string)$this->brick_xml_properties->spinnerSelection) {
            case 80:
                $destination = 'Touch position';
                break;
            case 81:
                $destination = 'Random position';
                break;
            case 82:
                $destination =
                  (string)$this->brick_xml_properties->destinationSprite
                    ->xpath($this->brick_xml_properties->destinationSprite[Constants::REFERENCE_ATTRIBUTE])[0][Constants::NAME_ATTRIBUTE];
                break;
            default:
                $destination = 'unknown';
                break;
        }
        $this->caption = "Go to " . $destination;

        $this->setImgFile(Constants::MOTION_BRICK_IMG);
    }
}