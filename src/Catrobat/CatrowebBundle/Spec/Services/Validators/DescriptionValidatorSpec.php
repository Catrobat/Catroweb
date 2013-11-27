<?php

namespace Catrobat\CatrowebBundle\Spec\Services\Validators;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;


class DescriptionValidatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Catrobat\CatrowebBundle\Services\Validators\DescriptionValidator');
    }
    
    /**
     * @param \Catrobat\CatrowebBundle\Model\ExtractedCatrobatFile $file
     */
    function it_throws_an_exception_if_the_description_is_too_long($file)
    {
      $description = "";
      for ($i = 0; $i <= 1001; $i++)
      {
      $description = $description . "a";
      }
      $file->getDescription()->willReturn($description);
      $this->shouldThrow('Catrobat\CatrowebBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
    }
    
    /**
     * @param \Catrobat\CatrowebBundle\Model\ExtractedCatrobatFile $file
     */
    function it_throws_nothing_if_a_normal_description_is_validated($file)
    {
      $file->getDescription()->willReturn("Hello Text.");
      $this->shouldNotThrow('Catrobat\CatrowebBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
    }
}
