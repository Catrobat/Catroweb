<?php

namespace Catrobat\CoreBundle\Spec\Listeners;

use Symfony\Component\Translation\Tests\String;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\True;

class NameValidatorSpec extends ObjectBehavior
{

  /**
   * @param \Catrobat\CoreBundle\Services\RudeWordFilter $rudewordfilter
   */
  function let($rudewordfilter)
  {
    $this->beConstructedWith($rudewordfilter);
  }
  
  function it_is_initializable()
  {
      $this->shouldHaveType('Catrobat\CoreBundle\Listeners\NameValidator');
  }

  /**
   * @param \Catrobat\CoreBundle\Model\ExtractedCatrobatFile $file
   */
  function it_makes_sure_the_given_program_name_is_valid($file)
  {
    $file->getName()->willReturn("Jhon Doe");
    $this->shouldNotThrow('Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
  }


  /**
   * @param \Catrobat\CoreBundle\Model\ExtractedCatrobatFile $file
   */
  function it_throws_an_exception_if_the_name_is_null($file)
  {
    $file->getName()->willReturn(null);
    $this->shouldThrow('Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
  }

  /**
   * @param \Catrobat\CoreBundle\Model\ExtractedCatrobatFile $file
   */
  function it_throws_an_exception_if_the_name_is_empty($file)
  {
    $file->getName()->willReturn("");
    $this->shouldThrow('Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
  }

  /**
   * @param \Catrobat\CoreBundle\Model\ExtractedCatrobatFile $file
   */
  function it_throws_an_exception_if_the_name_is_too_long($file)
  {
    $name = "";
    for ($i = 0; $i <= 200; $i++)
    {
      $name = $name . "a";
    }
    $file->getName()->willReturn($name);
    $this->shouldThrow('Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
  }

  /**
   * @param \Catrobat\CoreBundle\Model\ExtractedCatrobatFile $file
   */
  function it_throws_an_exception_if_the_name_contains_a_rude_word($file, $rudewordfilter)
  {
    $file->getName()->willReturn("rudeword");
    $rudewordfilter->containsBadWord(Argument::any())->willReturn(true);
    $this->shouldThrow('Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
  }
    
}
