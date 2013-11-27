<?php

namespace Catrobat\CatrowebBundle\Services\Validators;

use Catrobat\CatrowebBundle\Model\ExtractedCatrobatFile;
use Symfony\Component\Finder\Finder;
use Catrobat\CatrowebBundle\Exceptions\InvalidCatrobatFileException;

class FileStructureValidator implements ExtractedCatrobatFileValidatorInterface
{
  
  /*
   * (non-PHPdoc) @see \Catrobat\CatrowebBundle\Services\Validators\ExtractedCatrobatFileValidatorInterface::validate()
   */
  public function validate(ExtractedCatrobatFile $file)
  {
    $finder = new Finder();
    $finder->in($file->getPath())
           ->exclude(array("sounds","images"))
           ->notPath("/^code.xml$/")
           ->notPath("/^screenshot.png$/")
           ->notPath("/^manual_screenshot.png$/")
           ->notPath("/^automatic_screenshot.png$/");
    
    if ($finder->count() > 0)
    {
      $list = array();
      foreach ($finder as $file)
      {
        $list[] = $file->getRelativePathname();
      }
      throw new InvalidCatrobatFileException("unexpected files found: " . implode(", ", $list));
    }
  }

}
