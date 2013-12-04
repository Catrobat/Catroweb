<?php

namespace Catrobat\CoreBundle\Spec\Services;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;

class CatrobatFileCompressorSpec extends ObjectBehavior
{
  function let()
  {
    $this->beConstructedWith(__SPEC_CACHE_DIR__);
  }
    
  function it_is_initializable()
  {
      $this->shouldHaveType('Catrobat\CoreBundle\Services\CatrobatFileCompressor');
  }
  
  function it_throws_an_exception_if_given_an_invalid_compress_directory()
  {
    $this->shouldThrow('Catrobat\CoreBundle\Exceptions\InvalidStorageDirectoryException')->during('__construct', array(__DIR__ . "/invalid_directory/"));
  }
  
  function it_compress_a_valid_directory()
  {
    $filesystem = new Filesystem();
    $path_to_file = __SPEC_FIXTURES_DIR__ . "GeneratedFixtures/base";
    $filesystem->mirror($path_to_file, __SPEC_CACHE_DIR__ . "/base/");    
    $this->compress("base");
    expect(is_file(__SPEC_CACHE_DIR__ ."/base.catrobat"))->toBe(true);    
  }
  
  function it_throws_an_exception_if_a_none_existing_directory_should_be_compressed()
  {
    $this->shouldThrow('Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException')->duringCompress("DOSENT_EXIST");
  }
  
}
