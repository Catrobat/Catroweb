<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Scripts;

use App\Project\CatrobatCode\Parser\Constants;

class ScriptFactory
{
  public static function generate(\SimpleXMLElement $script_xml_properties): BroadcastScript|CollisionScript|StartScript|UnknownScript|UserDefinedScript|WhenBGChangeScript|WhenBounceOffScript|WhenClonedScript|WhenConditionScript|WhenGamepadButtonScript|WhenNfcScript|WhenRaspiPinChangedScript|WhenScript|WhenTouchScript
  {
    return match ((string) $script_xml_properties[Constants::TYPE_ATTRIBUTE]) {
      Constants::START_SCRIPT => new StartScript($script_xml_properties),
      Constants::WHEN_SCRIPT => new WhenScript($script_xml_properties),
      Constants::WHEN_TOUCH_SCRIPT => new WhenTouchScript($script_xml_properties),
      Constants::BROADCAST_SCRIPT => new BroadcastScript($script_xml_properties),
      Constants::WHEN_CONDITION_SCRIPT => new WhenConditionScript($script_xml_properties),
      Constants::WHEN_BG_CHANGE_SCRIPT => new WhenBGChangeScript($script_xml_properties),
      Constants::WHEN_CLONED_SCRIPT => new WhenClonedScript($script_xml_properties),
      Constants::WHEN_GAME_PAD_BUTTON_SCRIPT => new WhenGamepadButtonScript($script_xml_properties),
      Constants::WHEN_RASPI_PIN_CHANGED_SCRIPT => new WhenRaspiPinChangedScript($script_xml_properties),
      Constants::WHEN_NFC_SCRIPT => new WhenNfcScript($script_xml_properties),
      Constants::WHEN_BOUNCE_OFF_SCRIPT => new WhenBounceOffScript($script_xml_properties),
      Constants::COLLISION_SCRIPT => new CollisionScript($script_xml_properties),
      Constants::USER_DEFINED_SCRIPT => new UserDefinedScript($script_xml_properties),
      default => new UnknownScript($script_xml_properties),
    };
  }
}
