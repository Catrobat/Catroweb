<?php

namespace Catrobat\CoreBundle\Spec\Listeners;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;


class DescriptionValidatorSpec extends ObjectBehavior
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

  /**
   * @param \Catrobat\CoreBundle\Model\ExtractedCatrobatFile $file
   */
  function it_throws_an_exception_if_the_descripiton_contains_a_rude_word($file, $rudewordfilter)
  {
    $file->getDescription()->willReturn("rudeword");
    $rudewordfilter->containsBadWord(Argument::any())->willReturn(true);
    $this->shouldThrow('Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
  }
}
