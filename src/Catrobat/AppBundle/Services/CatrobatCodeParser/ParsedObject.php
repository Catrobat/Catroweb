<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Scripts\ScriptFactory;

class ParsedObject
{
    protected $object_xml_properties;
    protected $name;
    protected $looks;
    protected $sounds;
    protected $scripts;

    public function __construct(\SimpleXMLElement $object_xml_properties)
    {
        $this->object_xml_properties = $object_xml_properties;
        $this->name = $this->resolveName();
        $this->looks = array();
        $this->sounds = array();
        $this->scripts = array();

        $this->parseLooks();
        $this->parseSounds();
        $this->parseScripts();
    }

    private function resolveName()
    {
        if ($this->object_xml_properties[Constants::NAME_ATTRIBUTE] != null)
            return $this->object_xml_properties[Constants::NAME_ATTRIBUTE];
        else
            return $this->object_xml_properties->name;
    }

    private function parseLooks()
    {
        foreach($this->object_xml_properties->lookList->children() as $look_xml_properties)
            $this->looks[] = new ParsedObjectAsset($this->dereference($look_xml_properties));
    }

    private function parseSounds()
    {
        foreach($this->object_xml_properties->soundList->children() as $sound_xml_properties)
            $this->sounds[] = new ParsedObjectAsset($this->dereference($sound_xml_properties));
    }

    private function parseScripts()
    {
        foreach($this->object_xml_properties->scriptList->children() as $script_xml_properties)
            $this->scripts[] = ScriptFactory::generate($this->dereference($script_xml_properties));
    }

    private function dereference($xml_properties)
    {
        if ($xml_properties[Constants::REFERENCE_ATTRIBUTE] != null)
            return $xml_properties->xpath($xml_properties[Constants::REFERENCE_ATTRIBUTE])[0];
        else
            return $xml_properties;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getLooks()
    {
        return $this->looks;
    }

    public function getSounds()
    {
        return $this->sounds;
    }

    public function getScripts()
    {
        return $this->scripts;
    }

    public function isGroup()
    {
        return false;
    }
}