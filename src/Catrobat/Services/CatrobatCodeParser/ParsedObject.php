<?php

namespace App\Catrobat\Services\CatrobatCodeParser;

use App\Catrobat\Services\CatrobatCodeParser\Scripts\ScriptFactory;

/**
 * Class ParsedObject
 * @package App\Catrobat\Services\CatrobatCodeParser
 */
class ParsedObject
{
  /**
   * @var \SimpleXMLElement
   */
  protected $object_xml_properties;
  /**
   * @var \SimpleXMLElement
   */
  protected $name;
  /**
   * @var array
   */
  protected $looks;
  /**
   * @var array
   */
  protected $sounds;
  /**
   * @var array
   */
  protected $scripts;

  /**
   * ParsedObject constructor.
   *
   * @param \SimpleXMLElement $object_xml_properties
   */
  public function __construct(\SimpleXMLElement $object_xml_properties)
  {
    $this->object_xml_properties = $object_xml_properties;
    $this->name = $this->resolveName();
    $this->looks = [];
    $this->sounds = [];
    $this->scripts = [];

    $this->parseLooks();
    $this->parseSounds();
    $this->parseScripts();
  }

  /**
   * @return \SimpleXMLElement
   */
  private function resolveName()
  {
    if ($this->object_xml_properties[Constants::NAME_ATTRIBUTE] != null)
    {
      return $this->object_xml_properties[Constants::NAME_ATTRIBUTE];
    }
    else
    {
      return $this->object_xml_properties->name;
    }
  }

  /**
   *
   */
  private function parseLooks()
  {
    foreach ($this->object_xml_properties->lookList->children() as $look_xml_properties)
      $this->looks[] = new ParsedObjectAsset($this->dereference($look_xml_properties));
  }

  /**
   *
   */
  private function parseSounds()
  {
    foreach ($this->object_xml_properties->soundList->children() as $sound_xml_properties)
      $this->sounds[] = new ParsedObjectAsset($this->dereference($sound_xml_properties));
  }

  /**
   *
   */
  private function parseScripts()
  {
    foreach ($this->object_xml_properties->scriptList->children() as $script_xml_properties)
      $this->scripts[] = ScriptFactory::generate($this->dereference($script_xml_properties));
  }

  /**
   * @param $xml_properties \SimpleXMLElement
   *
   * @return mixed
   */
  private function dereference($xml_properties)
  {
    if ($xml_properties[Constants::REFERENCE_ATTRIBUTE] != null)
    {
      return $xml_properties->xpath($xml_properties[Constants::REFERENCE_ATTRIBUTE])[0];
    }
    else
    {
      return $xml_properties;
    }
  }

  /**
   * @return \SimpleXMLElement
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * @return array
   */
  public function getLooks()
  {
    return $this->looks;
  }

  /**
   * @return array
   */
  public function getSounds()
  {
    return $this->sounds;
  }

  /**
   * @return array
   */
  public function getScripts()
  {
    return $this->scripts;
  }

  /**
   * @return bool
   */
  public function isGroup()
  {
    return false;
  }
}