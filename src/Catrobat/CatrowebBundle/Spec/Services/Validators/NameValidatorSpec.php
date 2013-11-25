<?php

namespace Catrobat\CatrowebBundle\Spec\Services\Validators;

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
    function it_throws_an_exception_if_the_name_is_incorrect($file)
    {
      $this->shouldThrow('Catrobat\CatrowebBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
    }
    
    
}
