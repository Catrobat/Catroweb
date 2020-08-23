<?php

namespace App\Catrobat\CatrobatCode\Parser;

use App\Catrobat\CatrobatCode\Parser\Scripts\ScriptFactory;
use SimpleXMLElement;

class ParsedObject
{
  protected SimpleXMLElement $object_xml_properties;

  protected SimpleXMLElement $name;

  protected array $looks;

  protected array $sounds;

  protected array $scripts;

  public function __construct(SimpleXMLElement $object_xml_properties)
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

  public function getName(): SimpleXMLElement
  {
    return $this->name;
  }

  public function getLooks(): array
  {
    return $this->looks;
  }

  public function getSounds(): array
  {
    return $this->sounds;
  }

  public function getScripts(): array
  {
    return $this->scripts;
  }

  public function isGroup(): bool
  {
    return false;
  }

  private function resolveName(): SimpleXMLElement
  {
    if (null != $this->object_xml_properties[Constants::NAME_ATTRIBUTE])
    {
      return $this->object_xml_properties[Constants::NAME_ATTRIBUTE];
    }

    return $this->object_xml_properties->name;
  }

  private function parseLooks(): void
  {
    foreach ($this->object_xml_properties->lookList->children() as $look_xml_properties)
    {
      $this->looks[] = new ParsedObjectAsset($this->dereference($look_xml_properties));
    }
  }

  private function parseSounds(): void
  {
    foreach ($this->object_xml_properties->soundList->children() as $sound_xml_properties)
    {
      $this->sounds[] = new ParsedObjectAsset($this->dereference($sound_xml_properties));
    }
  }

  private function parseScripts(): void
  {
    foreach ($this->object_xml_properties->scriptList->children() as $script_xml_properties)
    {
      $this->scripts[] = ScriptFactory::generate($this->dereference($script_xml_properties));
    }
  }

  private function dereference(SimpleXMLElement $xml_properties): SimpleXMLElement
  {
    if (null != $xml_properties[Constants::REFERENCE_ATTRIBUTE])
    {
      return $xml_properties->xpath($xml_properties[Constants::REFERENCE_ATTRIBUTE])[0];
    }

    return $xml_properties;
  }
}
