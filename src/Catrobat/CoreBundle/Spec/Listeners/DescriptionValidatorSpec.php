<?php

namespace Catrobat\CoreBundle\Spec\Listeners;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;


class DescriptionValidatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Catrobat\CoreBundle\Listeners\DescriptionValidator');
    }
    
    /**
     * @param \Catrobat\CoreBundle\Model\ExtractedCatrobatFile $file
     */
    function it_throws_an_exception_if_the_description_is_too_long($file)
    {
      $description = "";
      for ($i = 0; $i <= 1001; $i++)
      {
      $description = $description . "a";
      }
      $file->getDescription()->willReturn($description);
      $this->shouldThrow('Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
    }
    
    /**
     * @param \Catrobat\CoreBundle\Model\ExtractedCatrobatFile $file
     */
    function it_throws_nothing_if_a_normal_description_is_validated($file)
    {
      $file->getDescription()->willReturn("Hello Text.");
      $this->shouldNotThrow('Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
    }
}
