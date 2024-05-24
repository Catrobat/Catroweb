<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser;

abstract class ParsedObjectsContainer
{
  protected ?ParsedObject $background = null;

  protected array $objects = [];

  public function __construct(protected \SimpleXMLElement $xml_properties)
  {
    $this->parseObjects();
  }

  public function getBackground(): ?ParsedObject
  {
    return $this->background;
  }

  public function getObjects(): array
  {
    return $this->objects;
  }

  private function parseObjects(): void
  {
    /** @var ParsedObjectGroup|null $current_group */
    $current_group = null;
    foreach ($this->getAllObjectXMLProperties() as $object_xml_properties) {
      if (!$this->background instanceof ParsedObject) {
        $this->background = new ParsedObject($object_xml_properties);
      } else {
        switch ($object_xml_properties[Constants::TYPE_ATTRIBUTE]) {
          case Constants::GROUP_SPRITE_TYPE:
            $this->addCurrentGroup($current_group);
            $current_group = new ParsedObjectGroup($object_xml_properties);
            break;
          case Constants::GROUP_ITEM_SPRITE_TYPE:
            if ($current_group) {
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

  private function addCurrentGroup(mixed &$current_group): void
  {
    if ($current_group) {
      $this->objects[] = $current_group;
      $current_group = null;
    }
  }

  private function getAllObjectXMLProperties(): array
  {
    $all_object_xmls = [];
    foreach ($this->xml_properties->objectList->object as $object_xml_properties) {
      $object_xml = $this->dereference($object_xml_properties);

      if ($this->hasName($object_xml)) {
        $all_object_xmls[] = $object_xml;
        $all_object_xmls = array_merge($all_object_xmls, $this->getPointedObjectXMLProperties($object_xml));
      }
    }

    return $all_object_xmls;
  }

  private function getPointedObjectXMLProperties(\SimpleXMLElement $object_xml): array
  {
    $all_pointed_object_xmls = [];
    foreach ($object_xml->xpath('scriptList//'.Constants::POINTED_OBJECT_TAG) as $pointed_object_xml_properties) {
      $pointed_object_xml = $this->dereference($pointed_object_xml_properties);

      if ($this->hasName($pointed_object_xml)) {
        $all_pointed_object_xmls[] = $pointed_object_xml;
      }
    }

    return $all_pointed_object_xmls;
  }

  private function dereference(\SimpleXMLElement $object_xml_properties): \SimpleXMLElement
  {
    if (null != $object_xml_properties[Constants::REFERENCE_ATTRIBUTE]) {
      $attribute = $object_xml_properties->xpath($object_xml_properties[Constants::REFERENCE_ATTRIBUTE]->__toString());
      if (!isset($attribute[0])) {
        throw new \Exception('Invalid reference: '.$object_xml_properties[Constants::REFERENCE_ATTRIBUTE]);
      }

      return $this->dereference($attribute[0]);
    }

    return $object_xml_properties;
  }

  private function hasName(\SimpleXMLElement $object_xml_properties): bool
  {
    return null != $object_xml_properties[Constants::NAME_ATTRIBUTE] || 0 != count($object_xml_properties->name);
  }
}
