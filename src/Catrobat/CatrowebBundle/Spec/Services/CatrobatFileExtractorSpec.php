<?php

namespace Catrobat\CatrowebBundle\Spec\Services;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class CatrobatFileExtractorSpec extends ObjectBehavior
{

  function let()
  {
    $this->beConstructedWith(__SPEC_CACHE_DIR__);
  }

  function it_is_initializable()
  {
    $this->shouldHaveType('Catrobat\CatrowebBundle\Services\CatrobatFileExtractor');
  }

  function it_throws_an_exception_if_given_an_valid_extraction_directory()
  {
    $this->shouldThrow('Catrobat\CatrowebBundle\Exceptions\InvalidStorageDirectoryException')->during('__construct', array (
        __DIR__ . "/invalid_directory/" 
    ));
  }

  function it_extracts_a_valid_file()
  {
    $filesystem = new Filesystem();
    $valid_catrobat_file = new File(__SPEC_FIXTURES_DIR__ . "/compass.catrobat");
    $extracted_file = $this->extract($valid_catrobat_file);
    $extracted_file->shouldHaveType('Catrobat\CatrowebBundle\Model\ExtractedCatrobatFile');
  }

  function it_throws_an_exception_while_extracting_an_invalid_file()
  {
    $invalid_catrobat_file = new File(__SPEC_FIXTURES_DIR__ . "/invalid_archive.catrobat");
    $this->shouldThrow('Catrobat\CatrowebBundle\Exceptions\InvalidCatrobatFileException')->duringExtract($invalid_catrobat_file);
  }

}
