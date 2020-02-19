<?php

namespace App\Catrobat\Services\CatrobatCodeParser;

use SimpleXMLElement;

/**
 * Class ParsedObjectsContainer.
 */
abstract class ParsedObjectsContainer
{
  /**
   * @var SimpleXMLElement
   */
  protected $xml_properties;

  /**
   * @var null
   */
  protected $background;

  /**
   * @var array
   */
  protected $objects;

  /**
   * ParsedObjectsContainer constructor.
   */
  public function __construct(SimpleXMLElement $xml_properties)
  {
    $this->xml_properties = $xml_properties;
    $this->background = null;
    $this->objects = [];

    $this->parseObjects();
  }

  public function getBackground()
  {
    return $this->background;
  }

  /**
   * @return array
   */
  public function getObjects()
  {
    return $this->objects;
  }

  private function parseObjects()
  {
    /**
     * @var ParsedObjectGroup|null
     */
    $current_group = null;
    foreach ($this->getAllObjectXMLProperties() as $object_xml_properties)
    {
      if (null === $this->background)
      {
        $this->background = new ParsedObject($object_xml_properties);
      }
      else
      {
        switch ($object_xml_properties[Constants::TYPE_ATTRIBUTE])
        {
          case Constants::GROUP_SPRITE_TYPE:
            $this->addCurrentGroup($current_group);
            $current_group = new ParsedObjectGroup($object_xml_properties);
            break;
          case Constants::GROUP_ITEM_SPRITE_TYPE:
            if ($current_group)
            {
              $current_group->addObject(new ParsedObject($object_xml_properties));
            }
            break;
          default:
            $this->addCurrentGroup($current_group);
            $this->objects[] = new ParsedObject($object_xml_properties);
            break;
        }
      }
    }
    $this->addCurrentGroup($current_group);
  }

  /**
   * @param $current_group
   */
  private function addCurrentGroup(&$current_group)
  {
    if ($current_group)
    {
      $this->objects[] = $current_group;
      $current_group = null;
    }
  }

  /**
   * @return array
   */
  private function getAllObjectXMLProperties()
  {
    $all_object_xmls = [];
    foreach ($this->xml_properties->objectList->object as $object_xml_properties)
    {
      $object_xml = $this->dereference($object_xml_properties);

      if ($this->hasName($object_xml))
      {
        $all_object_xmls[] = $object_xml;
        $all_object_xmls = array_merge($all_object_xmls, $this->getPointedObjectXMLProperties($object_xml));
      }
    }

    return $all_object_xmls;
  }

  /**
   * @param $object_xml SimpleXMLElement
   *
   * @return array
   */
  private function getPointedObjectXMLProperties($object_xml)
  {
    $all_pointed_object_xmls = [];
    foreach ($object_xml->xpath('scriptList//'.Constants::POINTED_OBJECT_TAG) as $pointed_object_xml_properties)
    {
      $pointed_object_xml = $this->dereference($pointed_object_xml_properties);

      if ($this->hasName($pointed_object_xml))
      {
        $all_pointed_object_xmls[] = $pointed_object_xml;
      }
    }

    return $all_pointed_object_xmls;
  }

  /**
   * @param $object_xml_properties SimpleXMLElement
   *
   * @return mixed
   */
  private function dereference($object_xml_properties)
  {
    if (null != $object_xml_properties[Constants::REFERENCE_ATTRIBUTE])
    {
      return $this->dereference($object_xml_properties
        ->xpath($object_xml_properties[Constants::REFERENCE_ATTRIBUTE])[0]);
    }

    return $object_xml_properties;
  }

  /**
   * @param $object_xml_properties
   *
   * @return bool
   */
  private function hasName($object_xml_properties)
  {
    return (null != $object_xml_properties[Constants::NAME_ATTRIBUTE]) or (0 != count($object_xml_properties->name));
  }
}
