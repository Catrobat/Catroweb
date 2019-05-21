<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Services\ExtractedCatrobatFile;
use App\Catrobat\Events\ProgramBeforeInsertEvent;
use App\Catrobat\Exceptions\Upload\InvalidXmlException;

/**
 * Class ProgramXmlHeaderValidator
 * @package App\Catrobat\Listeners
 */
class ProgramXmlHeaderValidator
{
  /**
   * @param ProgramBeforeInsertEvent $event
   */
  public function onProgramBeforeInsert(ProgramBeforeInsertEvent $event)
  {
    $this->validate($event->getExtractedFile());
  }

  /**
   * @param ExtractedCatrobatFile $file
   */
  public function validate(ExtractedCatrobatFile $file)
  {
    $program_xml_properties = $file->getProgramXmlProperties();
    if (isset($program_xml_properties->header))
    {
      if (!(isset($program_xml_properties->header->applicationName) &&
        isset($program_xml_properties->header->applicationVersion) &&
        isset($program_xml_properties->header->catrobatLanguageVersion) &&
        isset($program_xml_properties->header->description) &&
        isset($program_xml_properties->header->mediaLicense) &&
        isset($program_xml_properties->header->platform) &&
        isset($program_xml_properties->header->platformVersion) &&
        isset($program_xml_properties->header->programLicense) &&
        isset($program_xml_properties->header->programName) &&
        isset($program_xml_properties->header->remixOf) &&
        isset($program_xml_properties->header->url) &&
        isset($program_xml_properties->header->userHandle) &&
        isset($program_xml_properties->header->buildType)))
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
