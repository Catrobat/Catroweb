<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Scripts;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use SimpleXMLElement;

class ScriptFactory
{
  /**
   * @return BroadcastScript|StartScript|WhenBGChangeScript|WhenConditionScript|WhenScript|WhenTouchScript|null
   */
  public static function generate(SimpleXMLElement $script_xml_properties)
  {
    $generated_script = null;

    switch ((string) $script_xml_properties[Constants::TYPE_ATTRIBUTE])
    {
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
        $generated_script = new WhenConditionScript($script_xml_properties);
        break;
      case Constants::WHEN_BG_CHANGE_SCRIPT:
        $generated_script = new WhenBGChangeScript($script_xml_properties);
        break;
      case Constants::WHEN_CLONED_SCRIPT:
        $generated_script = new WhenClonedScript($script_xml_properties);
        break;
      case Constants::WHEN_GAME_PAD_BUTTON_SCRIPT:
        $generated_script = new WhenGamepadButtonScript($script_xml_properties);
        break;
      case Constants::WHEN_RASPI_PIN_CHANGED_SCRIPT:
        $generated_script = new WhenRaspiPinChangedScript($script_xml_properties);
        break;
      case Constants::WHEN_NFC_SCRIPT:
        $generated_script = new WhenNfcScript($script_xml_properties);
        break;
      case Constants::WHEN_BOUNCE_OFF_SCRIPT:
        $generated_script = new WhenBounceOffScript($script_xml_properties);
        break;

      // deprecated scripts
      case Constants::COLLISION_SCRIPT:
        $generated_script = new CollisionScript($script_xml_properties);
        break;

      // other scripts
      default:
        $generated_script = new UnknownScript($script_xml_properties);
        break;
    }

    return $generated_script;
  }
}
