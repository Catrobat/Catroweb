<?php

namespace App\Catrobat\Services\CatrobatCodeParser;


/**
 * Class ParsedObjectAsset
 * @package App\Catrobat\Services\CatrobatCodeParser
 */
class ParsedObjectAsset
{
  /**
   * @var \SimpleXMLElement
   */
  protected $asset_xml_properties;
  /**
   * @var \SimpleXMLElement
   */
  protected $name;
  /**
   * @var \SimpleXMLElement
   */
  protected $file_name;

  /**
   * ParsedObjectAsset constructor.
   *
   * @param \SimpleXMLElement $asset_xml_properties
   */
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

  /**
   * @return \SimpleXMLElement
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * @return \SimpleXMLElement
   */
  public function getFileName()
  {
    return $this->file_name;
  }
}