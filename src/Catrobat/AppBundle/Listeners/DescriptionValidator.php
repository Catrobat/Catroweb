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

class DescriptionValidator
{
    private $rudeWordFilter;
    private $container;

    public function __construct(RudeWordFilter $rudeWordFilter, ContainerInterface $container)
    {
        $this->rudeWordFilter = $rudeWordFilter;
        $this->container = $container;
    }

    public function onProgramBeforeInsert(ProgramBeforeInsertEvent $event)
    {
        $this->validate($event->getExtractedFile());
    }

    public function validate(ExtractedCatrobatFile $file)
    {
      $max_description_size = $this->container->get('kernel')->getContainer()
        ->getParameter("catrobat.max_description_upload_size");
        if (strlen($file->getDescription()) > $max_description_size) {
            throw new DescriptionTooLongException();
        }

        if ($this->rudeWordFilter->containsRudeWord($file->getDescription())) {
            throw new RudewordInDescriptionException();
        }
    }
}
