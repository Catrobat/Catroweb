<?php

namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Events\ProgramBeforeInsertEvent;
use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

class VersionValidator
{
    const VERSION = "0.92";


    public function onProgramBeforeInsert(ProgramBeforeInsertEvent $event)
    {
        $this->validate($event->getExtractedFile()->getProgramXmlProperties());
    }


    public function validate(\SimpleXMLElement $xml)
    {
        if (version_compare($xml->header->catrobatLanguageVersion, self::VERSION, "<")) {
            throw new InvalidCatrobatFileException("catrobat language version too old", StatusCode::OLD_CATROBAT_LANGUAGE);
        }
    }
}
