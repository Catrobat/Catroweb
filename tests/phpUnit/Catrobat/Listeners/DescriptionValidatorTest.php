<?php

namespace Tests\phpUnit\Catrobat\Listeners;

use App\Catrobat\Exceptions\Upload\DescriptionTooLongException;
use App\Catrobat\Exceptions\Upload\RudewordInDescriptionException;
use App\Catrobat\Listeners\DescriptionValidator;
use App\Catrobat\Services\ExtractedCatrobatFile;
use App\Catrobat\Services\RudeWordFilter;
use Doctrine\ORM\NonUniqueResultException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \App\Catrobat\Listeners\DescriptionValidator
 */
class DescriptionValidatorTest extends TestCase
{
  private DescriptionValidator $description_validator;

  /**
   * @var MockObject|RudeWordFilter
   */
  private $rude_word_filter;

  protected function setUp(): void
  {
    $this->rude_word_filter = $this->createMock(RudeWordFilter::class);

    $this->description_validator = new DescriptionValidator($this->rude_word_filter);
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(DescriptionValidator::class, $this->description_validator);
  }

  /**
   * @throws NonUniqueResultException
   */
  public function testThrowsAnExceptionIfTheDescriptionIsTooLong(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $description = str_pad('a', 10_001, 'a');
    $file->expects($this->atLeastOnce())->method('getDescription')->willReturn($description);
    $this->expectException(DescriptionTooLongException::class);
    $this->description_validator->validate($file);
  }

  /**
   * @throws NonUniqueResultException
   */
  public function testThrowsNothingIfANormalDescriptionIsValidated(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $file->expects($this->atLeastOnce())->method('getDescription')->willReturn('Hello Text.');
    $this->description_validator->validate($file);
  }

  /**
   * @throws NonUniqueResultException
   */
  public function testThrowsAnExceptionIfTheDescriptionContainsARudeWord(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $file->expects($this->atLeastOnce())->method('getDescription')->willReturn('rudeword');
    $this->rude_word_filter->expects($this->atLeastOnce())->method('containsRudeWord')->willReturn(true);
    $this->expectException(RudewordInDescriptionException::class);
    $this->description_validator->validate($file);
  }
}
