<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser;


class ParsedObjectGroup
{
    protected $object_group_xml_properties;
    protected $name;
    protected $objects;

    public function __construct(\SimpleXMLElement $object_group_xml_properties)
    {
        $this->object_group_xml_properties = $object_group_xml_properties;
        $this->name = $this->resolveName();
        $this->objects = array();
    }

    private function resolveName()
    {
        if ($this->object_group_xml_properties[Constants::NAME_ATTRIBUTE] != null)
            return $this->object_group_xml_properties[Constants::NAME_ATTRIBUTE];
        else
            return $this->object_group_xml_properties->name;
    }

    public function addObject($object)
    {
        $this->objects[] = $object;
    }
    
    public function getName()
    {
        return $this->name;
    }

    public function getObjects()
    {
        return $this->objects;
    }

    public function isGroup()
    {
        return true;
    }
}