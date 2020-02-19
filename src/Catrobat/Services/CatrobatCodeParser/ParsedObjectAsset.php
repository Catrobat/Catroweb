<?php

namespace App\Catrobat\Services\CatrobatCodeParser;

use SimpleXMLElement;

/**
 * Class ParsedObjectAsset.
 */
class ParsedObjectAsset
{
  /**
   * @var SimpleXMLElement
   */
  protected $asset_xml_properties;
  /**
   * @var SimpleXMLElement
   */
  protected $name;
  /**
   * @var SimpleXMLElement
   */
  protected $file_name;

  /**
   * ParsedObjectAsset constructor.
   */
  public function __construct(SimpleXMLElement $asset_xml_properties)
  {
    $this->asset_xml_properties = $asset_xml_properties;
    $this->name = $asset_xml_properties[Constants::NAME_ATTRIBUTE];
    $this->file_name = rawurlencode($asset_xml_properties['fileName']);
  }

  /**
   * @return SimpleXMLElement
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * @return SimpleXMLElement
   */
  public function getFileName()
  {
    return $this->file_name;
  }
}
