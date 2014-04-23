<?php

namespace Catrobat\CoreBundle\Services\Validators;

use Catrobat\CoreBundle\Model\ExtractedCatrobatFile;
use Symfony\Component\Finder\Finder;
use Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\CoreBundle\Events\ProgramBeforeInsertEvent;

class OnlyDefinedImagesValidator
{
  
  public function onProgramBeforeInsert(ProgramBeforeInsertEvent $event)
  {
    $this->validate($event->getExtractedFile());
  }
  
  public function validate(ExtractedCatrobatFile $file)
  {
    
    $files_in_xml = OnlyDefinedImagesValidator::getImagesFromXml($file->getProgramXmlProperties());
    $files_in_directory = OnlyDefinedImagesValidator::getImagesFromImageDirectory($file->getPath());
    
    $files = array_diff($files_in_directory, $files_in_xml);
    if (count($files) > 0)
    {
      throw new InvalidCatrobatFileException(InvalidCatrobatFileException::UNEXPECTED_FILE);
    }
    $files = array_diff($files_in_xml, $files_in_directory);
    if (count($files) > 0)
    {
      throw new InvalidCatrobatFileException(InvalidCatrobatFileException::IMAGE_MISSING);
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