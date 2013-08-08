<?php
namespace Catrobat\CatrowebBundle\Helper;


use Catrobat\CatrowebBundle\Exceptions\InvalidCatrobatFileException;
class ProjectDirectoryValidator
{
  
  private function checkAndUpdateLicence($project_properties)
  {
    //  $licence = $project_properties->header->programLicense;
    //  $licence->{0} = "asd";
    //    dom_import_simplexml($licence)->nodeValue = "1";
    //  $bla = dom_import_simplexml($licence)->nodeValue;
    //  $info['licence'] = $licence;
    //    $project_properties->asXML($this->extract_dir . "code.xml");
    //return  $project_properties;
  }
  
  private function parseXML($base_dir)
  {
    $info = array();
    $project_properties = simplexml_load_file($base_dir . "code.xml");
    if ($project_properties === false)
    {
      throw new InvalidCatrobatFileException("error parsing config xml");
    }
    $info['name'] = (string)$project_properties->header->programName;
    $info['description'] = (string)$project_properties->header->description;
    $info['application_version'] = (string)$project_properties->header->applicationVersion;
    if (is_file($base_dir . "screenshot.png"))
    {
      $info['screenshot'] = $base_dir . "screenshot.png";
    }
    else if (is_file($base_dir . "manual_screenshot.png"))
    {
      $info['screenshot'] = $base_dir . "manual_screenshot.png";
    }
    else if (is_file($base_dir . "automatic_screenshot.png"))
    {
      $info['screenshot'] = $base_dir . "automatic_screenshot.png";
    }
  
    return $info;
  }
  
  public function getProjectInfo($directory)
  {
     return $this->parseXML($directory);
  }
}