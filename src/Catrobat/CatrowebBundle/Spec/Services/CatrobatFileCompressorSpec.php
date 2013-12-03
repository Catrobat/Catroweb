<?php

namespace Catrobat\CatrowebBundle\Spec\Services;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

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
}
