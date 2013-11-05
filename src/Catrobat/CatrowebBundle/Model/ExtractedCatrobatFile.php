<?php
namespace Catrobat\CatrowebBundle\Model;


use Catrobat\CatrowebBundle\Exceptions\InvalidCatrobatFileException;
class ExtractedCatrobatFile
{
  protected $path;
  protected $project_xml_properties;

  public function __construct($base_dir)
  {
    $this->path = $base_dir;
    try
    {
      $this->project_xml_properties = simplexml_load_file($base_dir . "code.xml");
    }
    catch (\Exception $e)
    {
      throw new InvalidCatrobatFileException("missing project xml");
    }
    if ($this->project_xml_properties === false)
    {
      throw new InvalidCatrobatFileException("error parsing config xml");
    }
  }

  public function getName()
  {
    return (string)$this->project_xml_properties->header->programName;
  }
  
  public function getDescription()
  {
    return (string)$this->project_xml_properties->header->description;
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
    $info['application_version'] = (string)$$this->project_properties->header->applicationVersion;
  }
  
  public function getPath()
  {
    return $this->path;
  }
  
  public function getProjectXmlProperties()
  {
    return $this->project_xml_properties;
  }
}