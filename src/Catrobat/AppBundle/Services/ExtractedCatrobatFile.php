<?php
namespace Catrobat\AppBundle\Services;

use Catrobat\AppBundle\CatrobatCode\StatementFactory;
use Catrobat\AppBundle\Exceptions\Upload\InvalidXmlException;
use Catrobat\AppBundle\Exceptions\Upload\MissingXmlException;
use Symfony\Component\Finder\Finder;

class ExtractedCatrobatFile
{

    protected $path;

    protected $web_path;

    protected $dir_hash;

    protected $program_xml_properties;

    public function __construct($base_dir, $base_path, $dir_hash)
    {
        $this->path = $base_dir;
        $this->dir_hash = $dir_hash;
        $this->web_path = $base_path;
        
        if (! file_exists($base_dir . 'code.xml')) {
            throw new MissingXmlException();
        }
        
        $content = file_get_contents($base_dir . 'code.xml');
        if ($content === false) {
            throw new InvalidXmlException();
        }
        $content = str_replace('&#x0;', '', $content, $count);
        $this->program_xml_properties = @simplexml_load_string($content);
        if ($this->program_xml_properties === false) {
            throw new InvalidXmlException();
        }
    }

    public function getName()
    {
        return (string)$this->program_xml_properties->header->programName;
    }

    public function getLanguageVersion()
    {
        return (string)$this->program_xml_properties->header->catrobatLanguageVersion;
    }

    public function getDescription()
    {
        return (string)$this->program_xml_properties->header->description;
    }

    public function getDirHash()
    {
        return $this->dir_hash;
    }

    public function getTags()
    {
        $tags = (string)$this->program_xml_properties->header->tags;
        if (strlen($tags) > 0)
        {
            return explode(',', (string)$this->program_xml_properties->header->tags);
        }
        return;
    }

    public function getContainingImagePaths()
    {
        $finder = new Finder();
        $file_paths = array();

        if ($this->hasScenes()) {
            $finder->files()->in($this->path . '/*/images/');
            foreach ($finder as $file) {
                $parts = explode($this->dir_hash . '/', $file->getRealPath());
                $file_paths[] = '/' . $this->web_path . $parts[1];
            }
        } else {
            $finder->files()->in($this->path . 'images/');
            foreach ($finder as $file) {
                $file_paths[] = '/' . $this->web_path . 'images/' . $file->getFilename();
            }
        }
        return $file_paths;
    }

    public function getContainingSoundPaths()
    {
        $finder = new Finder();
        $file_paths = array();

        if ($this->hasScenes()) {
            $finder->files()->in($this->path . '/*/sounds/');
            foreach ($finder as $file) {
                $parts = explode($this->dir_hash . '/', $file->getRealPath());
                $file_paths[] = '/' . $this->web_path . $parts[1];
            }
        } else {
            $finder->files()->in($this->path . 'sounds/');
            foreach ($finder as $file) {
                $file_paths[] = '/' . $this->web_path . 'sounds/' . $file->getFilename();
            }
        }
        return $file_paths;
    }

    public function getContainingStrings()
    {
        $xml = file_get_contents($this->path . 'code.xml');
        $matches = array();
        preg_match_all('#>(.*[a-zA-Z].*)<#', $xml, $matches);
        
        return array_unique($matches[1]);
    }

    public function getScreenshotPath()
    {
        $screenshot_path = null;
        if (is_file($this->path . 'screenshot.png')) {
            $screenshot_path = $this->path . 'screenshot.png';
        } elseif (is_file($this->path . 'manual_screenshot.png')) {
            $screenshot_path = $this->path . 'manual_screenshot.png';
        } elseif (is_file($this->path . 'automatic_screenshot.png')) {
            $screenshot_path = $this->path . 'automatic_screenshot.png';
        }
        $finder = new Finder();
        //$finder->in($this->path->getPath())->directories()->name("automatic_screenshot.png")
        if($screenshot_path === null)
        {
            $fu = $finder->in($this->path)->files()->name("manual_screenshot.png");

            foreach ($fu as $file)
            {
                $screenshot_path = $file->getPathname();
                break;
            }
        }
        if($screenshot_path === null) {
            $fu = $finder->in($this->path)->files()->name("automatic_screenshot.png");

            foreach ($fu as $file)
            {
                $screenshot_path = $file->getPathname();
                break;
            }
        }

        return $screenshot_path;
    }

    public function getApplicationVersion()
    {
        return (string)$this->program_xml_properties->header->applicationVersion;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getWebPath()
    {
        return $this->web_path;
    }

    public function getProgramXmlProperties()
    {
        return $this->program_xml_properties;
    }

    public function saveProgramXmlProperties()
    {
        $this->program_xml_properties->asXML($this->path . 'code.xml');
        
        $xml_string = file_get_contents($this->path . 'code.xml');
        $xml_string = preg_replace('/<receivedMessage>(.*)&lt;-&gt;ANYTHING<\/receivedMessage>/', '<receivedMessage>$1&lt;&#x0;-&#x0;&gt;&#x0;ANYTHING&#x0;</receivedMessage>', $xml_string);
        $xml_string = preg_replace('/<receivedMessage>(.*)&lt;-&gt;(.*)<\/receivedMessage>/', '<receivedMessage>$1&lt;&#x0;-&#x0;&gt;$2</receivedMessage>', $xml_string);
        
        if ($xml_string != null)
        {
            file_put_contents($this->path . 'code.xml', $xml_string);
        }
    }

    public function getContainingCodeObjects()
    {
        $objects = array();
        $objectList = $this->getCodeObjects();
        foreach ($objectList as $object) {
            $objects = $this->addObjectsToArray($objects, $object->getCodeObjectsRecursively());
        }

        return $objectList + $objects;
    }

    public function getCodeObjects()
    {
        $objects = array();
        $objectList = $this->program_xml_properties->objectList->children();
        foreach ($objectList as $object) {
            $newObject = $this->getObject($object);
            if ($newObject != null) {
                $objects[] = $newObject;
            }
        }
        return $objects;
    }

    private function getObject($objectTree)
    {
        $factory = new StatementFactory();
        return $factory->createObject($objectTree);
    }

    private function addObjectsToArray($objects, $objectsToAdd)
    {

        foreach ($objectsToAdd as $object) {
            $objects[] = $object;
        }
        return $objects;
    }

    public function hasScenes()
    {
        return count($this->program_xml_properties->xpath('//scenes')) != 0;
    }
}
