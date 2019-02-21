<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser;


/**
 * Class ParsedObjectGroup
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser
 */
class ParsedObjectGroup
{
  /**
   * @var \SimpleXMLElement
   */
  protected $object_group_xml_properties;
  /**
   * @var \SimpleXMLElement
   */
  protected $name;
  /**
   * @var array
   */
  protected $objects;

  /**
   * ParsedObjectGroup constructor.
   *
   * @param \SimpleXMLElement $object_group_xml_properties
   */
  public function __construct(\SimpleXMLElement $object_group_xml_properties)
  {
    $this->object_group_xml_properties = $object_group_xml_properties;
    $this->name = $this->resolveName();
    $this->objects = [];
  }

  /**
   * @return \SimpleXMLElement
   */
  private function resolveName()
  {
    if ($this->object_group_xml_properties[Constants::NAME_ATTRIBUTE] != null)
    {
      return $this->object_group_xml_properties[Constants::NAME_ATTRIBUTE];
    }
    else
    {
      return $this->object_group_xml_properties->name;
    }
  }

  /**
   * @param $object
   */
  public function addObject($object)
  {
    $this->objects[] = $object;
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
}