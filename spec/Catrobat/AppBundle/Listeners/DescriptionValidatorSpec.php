<?php

namespace spec\Catrobat\AppBundle\Listeners;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DescriptionValidatorSpec extends ObjectBehavior
{
    /**
   * @param \Catrobat\AppBundle\Services\RudeWordFilter $rudewordfilter
   */
  public function let($rudewordfilter)
  {
      $this->beConstructedWith($rudewordfilter);
  }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Catrobat\AppBundle\Listeners\DescriptionValidator');
    }

  /**
   * @param \Catrobat\AppBundle\Services\ExtractedCatrobatFile $file
   */
  public function it_throws_an_exception_if_the_description_is_too_long($file)
  {
      $description = '';
      for ($i = 0; $i <= 1001; ++$i) {
          $description = $description.'a';
      }
      $file->getDescription()->willReturn($description);
      $this->shouldThrow('Catrobat\AppBundle\Exceptions\Upload\DescriptionTooLongException')->duringValidate($file);
  }

  /**
   * @param \Catrobat\AppBundle\Services\ExtractedCatrobatFile $file
   */
  public function it_throws_nothing_if_a_normal_description_is_validated($file)
  {
      $file->getDescription()->willReturn('Hello Text.');
      $this->shouldNotThrow('Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
  }

  /**
   * @param \Catrobat\AppBundle\Services\ExtractedCatrobatFile $file
   */
  public function it_throws_an_exception_if_the_descripiton_contains_a_rude_word($file, $rudewordfilter)
  {
      $file->getDescription()->willReturn('rudeword');
      $rudewordfilter->containsRudeWord(Argument::any())->willReturn(true);
      $this->shouldThrow('Catrobat\AppBundle\Exceptions\Upload\RudewordInDescriptionException')->duringValidate($file);
  }
}
