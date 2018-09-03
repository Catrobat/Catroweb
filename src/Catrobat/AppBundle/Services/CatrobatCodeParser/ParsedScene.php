<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser;


use Symfony\Component\Config\Definition\Exception\Exception;

class ParsedScene extends ParsedObjectsContainer
{
  protected $name;

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

  public function getName()
  {
    return $this->name;
  }
}

