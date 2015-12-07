<?php

namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Services\ExtractedCatrobatFile;
use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\Events\ProgramBeforeInsertEvent;
use Catrobat\AppBundle\Services\RudeWordFilter;
use Catrobat\AppBundle\StatusCode;
use Catrobat\AppBundle\Exceptions\Upload\DescriptionTooLongException;
use Catrobat\AppBundle\Exceptions\Upload\RudewordInDescriptionException;

class DescriptionValidator
{
    private $rudeWordFilter;

    public function __construct(RudeWordFilter $rudeWordFilter)
    {
        $this->rudeWordFilter = $rudeWordFilter;
    }

    public function onProgramBeforeInsert(ProgramBeforeInsertEvent $event)
    {
        $this->validate($event->getExtractedFile());
    }

    public function validate(ExtractedCatrobatFile $file)
    {
        if (strlen($file->getDescription()) > 1000) {
            throw new DescriptionTooLongException();
        }

        if ($this->rudeWordFilter->containsRudeWord($file->getDescription())) {
            throw new RudewordInDescriptionException();
        }
    }
}
