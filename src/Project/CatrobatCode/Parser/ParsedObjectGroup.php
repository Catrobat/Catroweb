<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser;

class ParsedObjectGroup
{
  protected \SimpleXMLElement $name;

  protected array $objects = [];

  public function __construct(protected \SimpleXMLElement $object_group_xml_properties)
  {
    $this->name = $this->resolveName();
  }

  public function addObject(mixed $object): void
  {
    $this->objects[] = $object;
  }

  public function getName(): \SimpleXMLElement
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

  private function resolveName(): \SimpleXMLElement
  {
    if (null != $this->object_group_xml_properties[Constants::NAME_ATTRIBUTE]) {
      return $this->object_group_xml_properties[Constants::NAME_ATTRIBUTE];
    }

    return $this->object_group_xml_properties->name;
  }
}
