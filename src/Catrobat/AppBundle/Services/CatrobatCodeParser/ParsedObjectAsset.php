<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser;


class ParsedObjectAsset
{
  protected $asset_xml_properties;
  protected $name;
  protected $file_name;

  public function __construct(\SimpleXMLElement $asset_xml_properties)
  {
    $this->asset_xml_properties = $asset_xml_properties;

    if (count($asset_xml_properties->name) !== 0)
    {
      $this->name = $asset_xml_properties->name;
    }
    else
    {
      $this->name = $asset_xml_properties[Constants::NAME_ATTRIBUTE];
    }

    $this->file_name = $asset_xml_properties->fileName;
  }

  public function getName()
  {
    return $this->name;
  }

  public function getFileName()
  {
    return $this->file_name;
  }
}