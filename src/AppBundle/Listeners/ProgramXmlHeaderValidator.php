<?php

namespace AppBundle\Listeners;

use AppBundle\Model\ExtractedCatrobatFile;
use AppBundle\Exceptions\InvalidCatrobatFileException;
use AppBundle\Events\ProgramBeforeInsertEvent;
use AppBundle\StatusCode;

class ProgramXmlHeaderValidator
{
  
  public function onProgramBeforeInsert(ProgramBeforeInsertEvent $event)
  {
    $this->validate($event->getExtractedFile());
  }
  
  public function validate(ExtractedCatrobatFile $file)
  {
    $program_xml_properties = $file->getProgramXmlProperties();
    if(isset($program_xml_properties->header))
    {
      if(!(isset($program_xml_properties->header->applicationName) &&
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
        isset($program_xml_properties->header->userHandle)))
      {
        throw new InvalidCatrobatFileException("Program XML header information missing",StatusCode::INVALID_XML);
      }
    }
    else
    {
      throw new InvalidCatrobatFileException("No Program XML header found!",StatusCode::INVALID_XML);
    }

  }

}