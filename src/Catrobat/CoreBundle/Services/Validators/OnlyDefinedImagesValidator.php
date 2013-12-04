<?php

namespace Catrobat\CoreBundle\Services\Validators;

use Catrobat\CoreBundle\Model\ExtractedCatrobatFile;
use Symfony\Component\Finder\Finder;
use Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException;

class OnlyDefinedImagesValidator implements ExtractedCatrobatFileValidatorInterface
{
  
  /*
   * (non-PHPdoc) @see \Catrobat\CoreBundle\Services\Validators\ExtractedCatrobatFileValidatorInterface::validate()
   */
  public function validate(ExtractedCatrobatFile $file)
  {
    
    $files_in_xml = OnlyDefinedImagesValidator::getImagesFromXml($file->getProjectXmlProperties());
    $files_in_directory = OnlyDefinedImagesValidator::getImagesFromImageDirectory($file->getPath());
    
    $files = array_diff($files_in_directory, $files_in_xml);
    if (count($files) > 0)
    {
      throw new InvalidCatrobatFileException("Unexpected files found: " . implode(", ", $files));
    }
    $files = array_diff($files_in_xml, $files_in_directory);
    if (count($files) > 0)
    {
      throw new InvalidCatrobatFileException("Files missing: " . implode(", ", $files));
    }
  }

  protected static function getImagesFromImageDirectory($base_path)
  {
    $images = array();
    $finder = new Finder();
    $finder->notPath("/^.nomedia$/")->ignoreDotFiles(false)->ignoreVCS(false)->in($base_path . "/images/");
    foreach ($finder as $file)
    {
      $images[] = $file->getRelativePathname();
    }
    return $images;
  }
  
  protected static function getImagesFromXml($xml)
  {
    $defined_file_nodes = $xml->xpath('/program/objectList/object/lookList/look/fileName');
    $defined_files = array();
    while (list (, $node) = each($defined_file_nodes))
    {
      $defined_files[] = $node;
    }
    return array_unique($defined_files);
  }
  
}