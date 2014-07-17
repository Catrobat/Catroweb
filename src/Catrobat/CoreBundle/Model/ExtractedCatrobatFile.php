<?php
namespace Catrobat\CoreBundle\Model;

use Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\CoreBundle\StatusCode;

class ExtractedCatrobatFile
{
  protected $path;
  protected $program_xml_properties;

  public function __construct($base_dir)
  {
    $this->path = $base_dir;
    
    if (!file_exists($base_dir . "code.xml"))
    {
      throw new InvalidCatrobatFileException(StatusCode::PROJECT_XML_MISSING);
    }
    
    $this->program_xml_properties = @simplexml_load_file($base_dir . "code.xml");
    if ($this->program_xml_properties === false)
    {
      throw new InvalidCatrobatFileException(StatusCode::INVALID_XML);
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
  
  public function getScreenshotPath()
  {
    $screenshot_path = "";
    if (is_file($this->path . "screenshot.png"))
    {
      $screenshot_path = $this->path . "screenshot.png";
    }
    else if (is_file($this->path . "manual_screenshot.png"))
    {
      $screenshot_path = $this->path . "manual_screenshot.png";
    }
    else if (is_file($this->path . "automatic_screenshot.png"))
    {
      $screenshot_path = $this->path . "automatic_screenshot.png";
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
  
  public function getProgramXmlProperties()
  {
    return $this->program_xml_properties;
  }
}