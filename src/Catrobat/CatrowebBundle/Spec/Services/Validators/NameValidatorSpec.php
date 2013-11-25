<?php

namespace Catrobat\CatrowebBundle\Spec\Services\Validators;

use Symfony\Component\Translation\Tests\String;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NameValidatorSpec extends ObjectBehavior
{
  
    function it_is_initializable()
    {
        $this->shouldHaveType('Catrobat\CatrowebBundle\Services\Validators\NameValidator');
    }

    /**
     * @param \Catrobat\CatrowebBundle\Model\ExtractedCatrobatFile $file
     */
    function it_throws_an_exception_if_the_name_is_null($file)
    {
      $file->getName()->willReturn(null);
      $this->shouldThrow('Catrobat\CatrowebBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
    }
    
    /**
     * @param \Catrobat\CatrowebBundle\Model\ExtractedCatrobatFile $file
     */
    function it_throws_an_exception_if_the_name_is_empty($file)
    {
      $file->getName()->willReturn("");
      $this->shouldThrow('Catrobat\CatrowebBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
    }
    
    /**
     * @param \Catrobat\CatrowebBundle\Model\ExtractedCatrobatFile $file
     */
    function it_throws_an_exception_if_the_name_is_too_long($file)
    {
      $name = "";
      for ($i = 0; $i <= 200; $i++)
      {
        $name = $name . "a";
      }
      $file->getName()->willReturn($name);
      $this->shouldThrow('Catrobat\CatrowebBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
    }
    
}
