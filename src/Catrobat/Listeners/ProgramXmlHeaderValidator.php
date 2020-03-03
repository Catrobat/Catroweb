<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Events\ProgramBeforeInsertEvent;
use App\Catrobat\Exceptions\Upload\InvalidXmlException;
use App\Catrobat\Services\ExtractedCatrobatFile;

/**
 * Class ProgramXmlHeaderValidator.
 */
class ProgramXmlHeaderValidator
{
  public function onProgramBeforeInsert(ProgramBeforeInsertEvent $event)
  {
    $this->validate($event->getExtractedFile());
  }

  public function validate(ExtractedCatrobatFile $file)
  {
    $program_xml_properties = $file->getProgramXmlProperties();
    if (isset($program_xml_properties->header))
    {
      if (!(isset($program_xml_properties->header->applicationName, $program_xml_properties->header->applicationVersion, $program_xml_properties->header->catrobatLanguageVersion, $program_xml_properties->header->description, $program_xml_properties->header->mediaLicense, $program_xml_properties->header->platform, $program_xml_properties->header->platformVersion, $program_xml_properties->header->programLicense, $program_xml_properties->header->programName, $program_xml_properties->header->remixOf, $program_xml_properties->header->url, $program_xml_properties->header->userHandle)
        ))
      {
        throw new InvalidXmlException('Program XML header information missing');
      }
    }
    else
    {
      throw new InvalidXmlException('No Program XML header found!');
    }
  }
}
