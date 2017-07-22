<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Scripts;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

class ScriptFactory
{
    public static function generate(\SimpleXMLElement $script_xml_properties)
    {
        $generated_script = null;

        switch((string)$script_xml_properties[Constants::TYPE_ATTRIBUTE]) {
            case Constants::START_SCRIPT:
                $generated_script = new StartScript($script_xml_properties);
                break;
            case Constants::WHEN_SCRIPT:
                $generated_script = new WhenScript($script_xml_properties);
                break;
            case Constants::WHEN_TOUCH_SCRIPT:
                $generated_script = new WhenTouchScript($script_xml_properties);
                break;
            case Constants::BROADCAST_SCRIPT:
                $generated_script = new BroadcastScript($script_xml_properties);
                break;
            case Constants::WHEN_CONDITION_SCRIPT:
                $generated_script = new WhenTouchScript($script_xml_properties);
                break;
            case Constants::COLLISION_SCRIPT:
                $generated_script = new CollisionScript($script_xml_properties);
                break;
            case Constants::WHEN_BG_CHANGE_SCRIPT:
                $generated_script = new WhenBGChangeScript($script_xml_properties);
                break;
            case Constants::WHEN_CLONED_SCRIPT:
                $generated_script = new WhenClonedScript($script_xml_properties);
                break;
            default:
                $generated_script = new UnknownScript($script_xml_properties);
                break;
        }
        
        return $generated_script;
    }
}