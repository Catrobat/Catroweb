<?php

namespace Catrobat\AppBundle\Spec\Services;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;

class CatrobatFileCompressorSpec extends ObjectBehavior
{

  function it_is_initializable()
  {
      $this->shouldHaveType('Catrobat\AppBundle\Services\CatrobatFileCompressor');
  }
  
  function it_throws_an_exception_if_given_an_invalid_compress_directory()
  {
    $this->shouldThrow('Catrobat\AppBundle\Exceptions\InvalidStorageDirectoryException')->duringCompress(__DIR__ . "/invalid_directory/", __SPEC_CACHE_DIR__ . "/base/", "archivename");
  }
  
  function it_compress_a_valid_directory()
  {
    $filesystem = new Filesystem();
    $path_to_file = __SPEC_FIXTURES_DIR__ . "GeneratedFixtures/base";
    $filesystem->mirror($path_to_file, __SPEC_CACHE_DIR__ . "/base/");    
    $this->compress(__SPEC_CACHE_DIR__ . "/base/",__SPEC_CACHE_DIR__, "base");
    expect(is_file(__SPEC_CACHE_DIR__ ."/base.catrobat"))->toBe(true); 
  }
}
