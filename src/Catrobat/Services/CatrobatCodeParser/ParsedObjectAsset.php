<?php

namespace App\Catrobat\Services\CatrobatCodeParser;

use SimpleXMLElement;

class ParsedObjectAsset
{
  protected SimpleXMLElement $asset_xml_properties;

  protected ?string $name;

  protected ?string $file_name;

  public function __construct(SimpleXMLElement $asset_xml_properties)
  {
    $this->asset_xml_properties = $asset_xml_properties;
    $this->name = $asset_xml_properties[Constants::NAME_ATTRIBUTE];
    $this->file_name = rawurlencode($asset_xml_properties['fileName']);
  }

  public function getName(): ?string
  {
    return $this->name;
  }

  public function getFileName(): ?string
  {
    return $this->file_name;
  }
}
