<?php

namespace App\Project\CatrobatFile;

use App\Project\Event\ProgramBeforeInsertEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProgramXmlHeaderValidatorEventSubscriber implements EventSubscriberInterface
{
  public function onProgramBeforeInsert(ProgramBeforeInsertEvent $event): void
  {
    $this->validate($event->getExtractedFile());
  }

  public function validate(ExtractedCatrobatFile $file): void
  {
    $program_xml_properties = $file->getProgramXmlProperties();
    if (isset($program_xml_properties->header)) {
      if (!(isset($program_xml_properties->header->applicationName, $program_xml_properties->header->applicationVersion, $program_xml_properties->header->catrobatLanguageVersion, $program_xml_properties->header->description, $program_xml_properties->header->mediaLicense, $program_xml_properties->header->platform, $program_xml_properties->header->platformVersion, $program_xml_properties->header->programLicense, $program_xml_properties->header->programName, $program_xml_properties->header->remixOf, $program_xml_properties->header->url, $program_xml_properties->header->userHandle)
      )) {
        throw new InvalidCatrobatFileException('errors.xml.invalid', 508, 'Program XML header information missing');
      }
    } else {
      throw new InvalidCatrobatFileException('errors.xml.invalid', 508, 'No Program XML header found!');
    }
  }

  public static function getSubscribedEvents(): array
  {
    return [ProgramBeforeInsertEvent::class => 'onProgramBeforeInsert'];
  }
}
