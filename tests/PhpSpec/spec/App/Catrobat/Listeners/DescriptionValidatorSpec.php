<?php

namespace tests\PhpSpec\spec\App\Catrobat\Listeners;

use App\Catrobat\Services\ExtractedCatrobatFile;
use App\Catrobat\Services\RudeWordFilter;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DescriptionValidatorSpec extends ObjectBehavior
{

  public function let(RudeWordFilter $rudewordfilter)
  {
    $this->beConstructedWith($rudewordfilter);
  }

  public function it_is_initializable()
  {
    $this->shouldHaveType('App\Catrobat\Listeners\DescriptionValidator');
  }

  public function it_throws_an_exception_if_the_description_is_too_long(ExtractedCatrobatFile $file)
  {
    $description = '';
    for ($i = 0; $i <= 10000; ++$i)
    {
      $description = $description . 'a';
    }
    $file->getDescription()->willReturn($description);
    $this->shouldThrow('App\Catrobat\Exceptions\Upload\DescriptionTooLongException')->duringValidate($file);
  }

  public function it_throws_nothing_if_a_normal_description_is_validated(ExtractedCatrobatFile $file)
  {
    $file->getDescription()->willReturn('Hello Text.');
    $this->shouldNotThrow('App\Catrobat\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
  }

  public function it_throws_an_exception_if_the_descripiton_contains_a_rude_word(ExtractedCatrobatFile $file, RudeWordFilter $rudewordfilter)
  {
    $file->getDescription()->willReturn('rudeword');
    $rudewordfilter->containsRudeWord(Argument::any())->willReturn(true);
    $this->shouldThrow('App\Catrobat\Exceptions\Upload\RudewordInDescriptionException')->duringValidate($file);
  }
}
