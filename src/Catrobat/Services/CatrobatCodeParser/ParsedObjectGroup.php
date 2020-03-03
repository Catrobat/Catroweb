<?php

namespace App\Catrobat\Services\CatrobatCodeParser;

use SimpleXMLElement;

/**
 * Class ParsedObjectGroup.
 */
class ParsedObjectGroup
{
  /**
   * @var SimpleXMLElement
   */
  protected $object_group_xml_properties;
  /**
   * @var SimpleXMLElement
   */
  protected $name;
  /**
   * @var array
   */
  protected $objects;

  /**
   * ParsedObjectGroup constructor.
   */
  public function __construct(SimpleXMLElement $object_group_xml_properties)
  {
    $this->object_group_xml_properties = $object_group_xml_properties;
    $this->name = $this->resolveName();
    $this->objects = [];
  }

  /**
   * @param $object
   */
  public function addObject($object)
  {
    $this->objects[] = $object;
  }

  /**
   * @return SimpleXMLElement
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * @return array
   */
  public function getObjects()
  {
    return $this->objects;
  }

  /**
   * @return bool
   */
  public function isGroup()
  {
    return true;
  }

  /**
   * @return SimpleXMLElement
   */
  private function resolveName()
  {
    if (null != $this->object_group_xml_properties[Constants::NAME_ATTRIBUTE])
    {
      return $this->object_group_xml_properties[Constants::NAME_ATTRIBUTE];
    }

    return $this->object_group_xml_properties->name;
  }
}
