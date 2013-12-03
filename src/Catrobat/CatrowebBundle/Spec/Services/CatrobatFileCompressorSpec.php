<?php

namespace Catrobat\CatrowebBundle\Spec\Services;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;

class CatrobatFileCompressorSpec extends ObjectBehavior
{
  function let()
  {
    $this->beConstructedWith(__DIR__ . "/../Cache/");
  }
    
  function it_is_initializable()
  {
      $this->shouldHaveType('Catrobat\CatrowebBundle\Services\CatrobatFileCompressor');
  }
  
  function it_throws_an_exception_if_given_an_invalid_compress_directory()
  {
    $this->shouldThrow('Catrobat\CatrowebBundle\Exceptions\InvalidStorageDirectoryException')->during('__construct', array(__DIR__ . "/invalid_directory/"));
  }
  
  function it_compress_a_valid_directory()
  {
    $filesystem = new Filesystem();
    $path_to_file = __SPEC_FIXTURES_DIR__ . "GeneratedFixtures/base";
    $filesystem->mirror($path_to_file, __DIR__ . "/../Cache/base/");    
    $this->compress("base");
    expect(is_file(__DIR__ . "/../Cache/base.catrobat"))->toBe(true);    
  }
  
  function it_throws_an_exception_if_a_none_existing_directory_should_be_compressed()
  {
    $this->shouldThrow('Catrobat\CatrowebBundle\Exceptions\InvalidCatrobatFileException')->duringCompress("DOSENT_EXIST");
  }
  
}
