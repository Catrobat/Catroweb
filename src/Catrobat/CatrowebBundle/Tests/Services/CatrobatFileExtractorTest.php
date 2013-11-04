<?php
namespace Catrobat\CatrowebBundle\Tests\Services;

use Catrobat\CatrowebBundle\Services\CatrobatFileExtractor;
use Catrobat\CatrowebBundle\Exceptions\InvalidCatrobatFileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Filesystem\Filesystem;

class CatrobatFileExtractorTest extends \PHPUnit_Framework_TestCase
{

  private $extractor;

  public function setUp()
  {
    $this->extractor = new CatrobatFileExtractor(__DIR__ . "/../Cache/");
  }

  public function testExtract()
  {
    $filesystem = new Filesystem();
    
    $valid_catrobat_file = new File(__DIR__ . "/../DataFixtures/CatrobatFiles/scaryghost.catrobat");
    $path_to_extracted_folder = $this->extractor->extract($valid_catrobat_file);
    $filesystem->remove($path_to_extracted_folder);
  }

  public function testExtractInvalidFile()
  {
    $this->setExpectedException('Catrobat\CatrowebBundle\Exceptions\InvalidCatrobatFileException');
    $invalid_catrobat_file = new File(__DIR__ . "/../DataFixtures/CatrobatFiles/invalid_archive.catrobat");
    $this->extractor->extract($invalid_catrobat_file);
  }
}
