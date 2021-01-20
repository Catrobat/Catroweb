<?php

namespace App\Catrobat\CatrobatCode\Parser;

use SimpleXMLElement;

class ParsedObjectGroup
{
  protected SimpleXMLElement$object_group_xml_properties;

  protected SimpleXMLElement $name;

  protected array $objects;

  public function __construct(SimpleXMLElement $object_group_xml_properties)
  {
    $this->object_group_xml_properties = $object_group_xml_properties;
    $this->name = $this->resolveName();
    $this->objects = [];
  }

  /**
   * @param mixed $object
   */
  public function addObject($object): void
  {
    $this->objects[] = $object;
  }

  public function getName(): SimpleXMLElement
  {
    return $this->name;
  }

  public function getObjects(): array
  {
    return $this->objects;
  }

  public function isGroup(): bool
  {
    return true;
  }

  private function resolveName(): SimpleXMLElement
  {
    if (null != $this->object_group_xml_properties[Constants::NAME_ATTRIBUTE])
    {
      return $this->object_group_xml_properties[Constants::NAME_ATTRIBUTE];
    }

    return $this->object_group_xml_properties->name;
  }
}
