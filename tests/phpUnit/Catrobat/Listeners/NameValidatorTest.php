<?php

namespace Tests\phpUnit\Catrobat\Listeners;

use App\Catrobat\Exceptions\Upload\MissingProgramNameException;
use App\Catrobat\Exceptions\Upload\NameTooLongException;
use App\Catrobat\Exceptions\Upload\RudewordInNameException;
use App\Catrobat\Listeners\NameValidator;
use App\Catrobat\Services\ExtractedCatrobatFile;
use App\Catrobat\Services\RudeWordFilter;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class NameValidatorTest extends TestCase
{
  private NameValidator $name_validator;

  /**
   * @var RudeWordFilter|MockObject
   */
  private $rude_word_filter;

  protected function setUp(): void
  {
    $this->rude_word_filter = $this->createMock(RudeWordFilter::class);
    $this->name_validator = new NameValidator($this->rude_word_filter);
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(NameValidator::class, $this->name_validator);
  }

  /**
   * @throws NoResultException
   * @throws NonUniqueResultException
   */
  public function testMakesSureTheGivenProgramNameIsValid(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $file->expects($this->atLeastOnce())->method('getName')->willReturn('John Doe');
    $this->name_validator->validate($file);
  }

  /**
   * @throws NonUniqueResultException
   * @throws NoResultException
   */
  public function testThrowsAnExceptionIfTheNameIsEmpty(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $file->expects($this->atLeastOnce())->method('getName')->willReturn('');
    $this->expectException(MissingProgramNameException::class);
    $this->name_validator->validate($file);
  }

  /**
   * @throws NoResultException
   * @throws NonUniqueResultException
   */
  public function testThrowsAnExceptionIfTheNameIsTooLong(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $name = str_pad('a', 201, 'a');
    $file->expects($this->atLeastOnce())->method('getName')->willReturn($name);
    $this->expectException(NameTooLongException::class);
    $this->name_validator->validate($file);
  }

  /**
   * @throws NoResultException
   * @throws NonUniqueResultException
   */
  public function testThrowsAnExceptionIfTheNameContainsARudeWord(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $file->expects($this->atLeastOnce())->method('getName')->willReturn('rudeword');
    $this->rude_word_filter->expects($this->atLeastOnce())->method('containsRudeWord')->willReturn(true);
    $this->expectException(RudewordInNameException::class);
    $this->name_validator->validate($file);
  }
}
