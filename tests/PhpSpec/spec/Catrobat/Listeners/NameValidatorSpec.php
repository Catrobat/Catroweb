<?php

namespace tests\PhpSpec\spec\App\Catrobat\Listeners;

use App\Catrobat\Services\ExtractedCatrobatFile;
use App\Catrobat\Services\RudeWordFilter;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NameValidatorSpec extends ObjectBehavior
{

  public function let(RudeWordFilter $rudewordfilter)
  {
    $this->beConstructedWith($rudewordfilter);
  }

  public function it_is_initializable()
  {
    $this->shouldHaveType('App\Catrobat\Listeners\NameValidator');
  }

  public function it_makes_sure_the_given_program_name_is_valid(ExtractedCatrobatFile $file)
  {
    $file->getName()->willReturn('Jhon Doe');
    $this->shouldNotThrow('App\Catrobat\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
  }

  public function it_throws_an_exception_if_the_name_is_null(ExtractedCatrobatFile $file)
  {
    $file->getName()->willReturn(null);
    $this->shouldThrow('App\Catrobat\Exceptions\Upload\MissingProgramNameException')->duringValidate($file);
  }

  public function it_throws_an_exception_if_the_name_is_empty(ExtractedCatrobatFile $file)
  {
    $file->getName()->willReturn('');
    $this->shouldThrow('App\Catrobat\Exceptions\Upload\MissingProgramNameException')->duringValidate($file);
  }

  public function it_throws_an_exception_if_the_name_is_too_long(ExtractedCatrobatFile $file)
  {
    $name = '';
    for ($i = 0; $i <= 200; ++$i)
    {
      $name = $name . 'a';
    }
    $file->getName()->willReturn($name);
    $this->shouldThrow('App\Catrobat\Exceptions\Upload\NameTooLongException')->duringValidate($file);
  }

  public function it_throws_an_exception_if_the_name_contains_a_rude_word(ExtractedCatrobatFile $file, RudeWordFilter $rudewordfilter)
  {
    $file->getName()->willReturn('rudeword');
    $rudewordfilter->containsRudeWord(Argument::any())->willReturn(true);
    $this->shouldThrow('App\Catrobat\Exceptions\Upload\RudewordInNameException')->duringValidate($file);
  }
}
