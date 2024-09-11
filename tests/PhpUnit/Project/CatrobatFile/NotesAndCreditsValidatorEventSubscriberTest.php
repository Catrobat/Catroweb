<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CatrobatFile;

use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\CatrobatFile\InvalidCatrobatFileException;
use App\Project\CatrobatFile\NotesAndCreditsValidatorEventSubscriber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(NotesAndCreditsValidatorEventSubscriber::class)]
class NotesAndCreditsValidatorEventSubscriberTest extends TestCase
{
  private NotesAndCreditsValidatorEventSubscriber $notes_and_credits_validator;

  #[\Override]
  protected function setUp(): void
  {
    $this->notes_and_credits_validator = new NotesAndCreditsValidatorEventSubscriber();
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(NotesAndCreditsValidatorEventSubscriber::class, $this->notes_and_credits_validator);
  }

  /**
   * @throws Exception
   */
  public function testThrowsAnExceptionIfNotesAndCreditsAreTooLong(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $notes_and_credits = str_pad('a', 3_001, 'a');
    $file->expects($this->atLeastOnce())->method('getNotesAndCredits')->willReturn($notes_and_credits);
    $this->expectException(InvalidCatrobatFileException::class);
    $this->notes_and_credits_validator->validate($file);
  }

  /**
   * @throws Exception
   */
  public function testThrowsNothingIfANormalNotesAndCreditsAreValidated(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $file->expects($this->atLeastOnce())->method('getNotesAndCredits')->willReturn('Hello Text.');
    $this->notes_and_credits_validator->validate($file);
  }
}
