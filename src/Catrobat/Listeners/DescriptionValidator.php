<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Services\ExtractedCatrobatFile;
use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\Events\ProgramBeforeInsertEvent;
use App\Catrobat\Services\RudeWordFilter;
use App\Catrobat\StatusCode;
use App\Catrobat\Exceptions\Upload\DescriptionTooLongException;
use App\Catrobat\Exceptions\Upload\RudewordInDescriptionException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DescriptionValidator
 * @package App\Catrobat\Listeners
 */
class DescriptionValidator
{
  /**
   * @var RudeWordFilter
   */
  private $rudeWordFilter;
  /**
   * @var int
   */
  private $max_description_size;

  /**
   * DescriptionValidator constructor.
   *
   * @param RudeWordFilter $rudeWordFilter
   */
  public function __construct(RudeWordFilter $rudeWordFilter)
  {
    $this->rudeWordFilter = $rudeWordFilter;
    $this->max_description_size = 10000;
  }

  /**
   * @param ProgramBeforeInsertEvent $event
   *
   * @throws \Doctrine\ORM\NonUniqueResultException
   */
  public function onProgramBeforeInsert(ProgramBeforeInsertEvent $event)
  {
    $this->validate($event->getExtractedFile());
  }

  /**
   * @param ExtractedCatrobatFile $file
   *
   * @throws \Doctrine\ORM\NonUniqueResultException
   */
  public function validate(ExtractedCatrobatFile $file)
  {
    if (strlen($file->getDescription()) > $this->max_description_size)
    {
      throw new DescriptionTooLongException();
    }

    if ($this->rudeWordFilter->containsRudeWord($file->getDescription()))
    {
      throw new RudewordInDescriptionException();
    }
  }
}
