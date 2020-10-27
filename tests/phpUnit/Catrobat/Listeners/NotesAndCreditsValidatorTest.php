<?php

namespace Tests\phpUnit\Catrobat\Listeners;

use App\Catrobat\Exceptions\Upload\NotesAndCreditsTooLongException;
use App\Catrobat\Exceptions\Upload\RudeWordInNotesAndCreditsException;
use App\Catrobat\Listeners\NotesAndCreditsValidator;
use App\Catrobat\Services\ExtractedCatrobatFile;
use App\Catrobat\Services\RudeWordFilter;
use Doctrine\ORM\NonUniqueResultException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \App\Catrobat\Listeners\NotesAndCreditsValidator
 */
class NotesAndCreditsValidatorTest extends TestCase
{
  private NotesAndCreditsValidator $notes_and_credits_validator;

  /**
   * @var MockObject|RudeWordFilter
   */
  private $rude_word_filter;

  protected function setUp(): void
  {
    $this->rude_word_filter = $this->createMock(RudeWordFilter::class);

    $this->notes_and_credits_validator = new NotesAndCreditsValidator($this->rude_word_filter);
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(NotesAndCreditsValidator::class, $this->notes_and_credits_validator);
  }

  /**
   * @throws NonUniqueResultException
   */
  public function testThrowsAnExceptionIfNotesAndCreditsAreTooLong(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $notes_and_credits = str_pad('a', 3_001, 'a');
    $file->expects($this->atLeastOnce())->method('getNotesAndCredits')->willReturn($notes_and_credits);
    $this->expectException(NotesAndCreditsTooLongException::class);
    $this->notes_and_credits_validator->validate($file);
  }

  /**
   * @throws NonUniqueResultException
   */
  public function testThrowsNothingIfANormalNotesAndCreditsAreValidated(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $file->expects($this->atLeastOnce())->method('getNotesAndCredits')->willReturn('Hello Text.');
    $this->notes_and_credits_validator->validate($file);
  }

  /**
   * @throws NonUniqueResultException
   */
  public function testThrowsAnExceptionIfNotesAndCreditsContainARudeWord(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $file->expects($this->atLeastOnce())->method('getNotesAndCredits')->willReturn('rudeword');
    $this->rude_word_filter->expects($this->atLeastOnce())->method('containsRudeWord')->willReturn(true);
    $this->expectException(RudeWordInNotesAndCreditsException::class);
    $this->notes_and_credits_validator->validate($file);
  }
}
