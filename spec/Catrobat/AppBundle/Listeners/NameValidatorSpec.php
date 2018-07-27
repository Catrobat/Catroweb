<?php

namespace spec\Catrobat\AppBundle\Listeners;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NameValidatorSpec extends ObjectBehavior
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
        $this->shouldHaveType('Catrobat\AppBundle\Listeners\NameValidator');
    }

  /**
   * @param \Catrobat\AppBundle\Services\ExtractedCatrobatFile $file
   */
  public function it_makes_sure_the_given_program_name_is_valid($file)
  {
      $file->getName()->willReturn('Jhon Doe');
      $this->shouldNotThrow('Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
  }

  /**
   * @param \Catrobat\AppBundle\Services\ExtractedCatrobatFile $file
   */
  public function it_throws_an_exception_if_the_name_is_null($file)
  {
      $file->getName()->willReturn(null);
      $this->shouldThrow('Catrobat\AppBundle\Exceptions\Upload\MissingProgramNameException')->duringValidate($file);
  }

  /**
   * @param \Catrobat\AppBundle\Services\ExtractedCatrobatFile $file
   */
  public function it_throws_an_exception_if_the_name_is_empty($file)
  {
      $file->getName()->willReturn('');
      $this->shouldThrow('Catrobat\AppBundle\Exceptions\Upload\MissingProgramNameException')->duringValidate($file);
  }

  /**
   * @param \Catrobat\AppBundle\Services\ExtractedCatrobatFile $file
   */
  public function it_throws_an_exception_if_the_name_is_too_long($file)
  {
      $name = '';
      for ($i = 0; $i <= 200; ++$i) {
          $name = $name.'a';
      }
      $file->getName()->willReturn($name);
      $this->shouldThrow('Catrobat\AppBundle\Exceptions\Upload\NameTooLongException')->duringValidate($file);
  }

  /**
   * @param \Catrobat\AppBundle\Services\ExtractedCatrobatFile $file
   */
  public function it_throws_an_exception_if_the_name_contains_a_rude_word($file, $rudewordfilter)
  {
      $file->getName()->willReturn('rudeword');
      $rudewordfilter->containsRudeWord(Argument::any())->willReturn(true);
      $this->shouldThrow('Catrobat\AppBundle\Exceptions\Upload\RudewordInNameException')->duringValidate($file);
  }
}
