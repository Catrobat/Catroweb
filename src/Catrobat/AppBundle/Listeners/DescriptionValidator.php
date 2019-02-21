<?php

namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Services\ExtractedCatrobatFile;
use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\Events\ProgramBeforeInsertEvent;
use Catrobat\AppBundle\Services\RudeWordFilter;
use Catrobat\AppBundle\StatusCode;
use Catrobat\AppBundle\Exceptions\Upload\DescriptionTooLongException;
use Catrobat\AppBundle\Exceptions\Upload\RudewordInDescriptionException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DescriptionValidator
 * @package Catrobat\AppBundle\Listeners
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
    $this->max_description_size = 4000;
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
