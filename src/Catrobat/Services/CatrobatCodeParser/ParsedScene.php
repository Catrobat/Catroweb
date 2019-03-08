<?php

namespace App\Catrobat\Services\CatrobatCodeParser;


use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Class ParsedScene
 * @package App\Catrobat\Services\CatrobatCodeParser
 */
class ParsedScene extends ParsedObjectsContainer
{
  /**
   * @var \SimpleXMLElement
   */
  protected $name;

  /**
   * ParsedScene constructor.
   *
   * @param \SimpleXMLElement $scene_xml_properties
   */
  public function __construct(\SimpleXMLElement $scene_xml_properties)
  {
    parent::__construct($scene_xml_properties);

    if (count($scene_xml_properties->name) === 0)
    {
      throw new Exception('Scene without name');
    }
    else
    {
      $this->name = $scene_xml_properties->name;
    }
  }

  /**
   * @return \SimpleXMLElement
   */
  public function getName()
  {
    return $this->name;
  }
}

