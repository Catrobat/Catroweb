<?php

namespace Tests\phpUnit\Catrobat\Listeners;

use App\Catrobat\Exceptions\Upload\NotesAndCreditsTooLongException;
use App\Catrobat\Listeners\NotesAndCreditsValidator;
use App\Catrobat\Services\ExtractedCatrobatFile;
use Doctrine\ORM\NonUniqueResultException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \App\Catrobat\Listeners\NotesAndCreditsValidator
 */
class NotesAndCreditsValidatorTest extends TestCase
{
  private NotesAndCreditsValidator $notes_and_credits_validator;

  protected function setUp(): void
  {
    $this->notes_and_credits_validator = new NotesAndCreditsValidator();
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(NotesAndCreditsValidator::class, $this->notes_and_credits_validator);
  }

  /**
   * @throws NonUniqueResultException
   * @throws \Doctrine\ORM\NoResultException
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
}
