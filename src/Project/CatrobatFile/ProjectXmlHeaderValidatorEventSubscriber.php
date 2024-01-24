<?php

namespace App\Project\CatrobatFile;

use App\Project\Event\ProjectBeforeInsertEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProjectXmlHeaderValidatorEventSubscriber implements EventSubscriberInterface
{
  public function onProjectBeforeInsert(ProjectBeforeInsertEvent $event): void
  {
    $this->validate($event->getExtractedFile());
  }

  public function validate(ExtractedCatrobatFile $file): void
  {
    $project_xml_properties = $file->getProjectXmlProperties();
    if (isset($project_xml_properties->header)) {
      if (!(isset($project_xml_properties->header->applicationName, $project_xml_properties->header->applicationVersion, $project_xml_properties->header->catrobatLanguageVersion, $project_xml_properties->header->description, $project_xml_properties->header->mediaLicense, $project_xml_properties->header->platform, $project_xml_properties->header->platformVersion, $project_xml_properties->header->programLicense, $project_xml_properties->header->programName, $project_xml_properties->header->remixOf, $project_xml_properties->header->url, $project_xml_properties->header->userHandle)
      )) {
        throw new InvalidCatrobatFileException('errors.xml.invalid', 508, 'Project XML header information missing');
      }
    } else {
      throw new InvalidCatrobatFileException('errors.xml.invalid', 508, 'No Project XML header found!');
    }
  }

  public static function getSubscribedEvents(): array
  {
    return [ProjectBeforeInsertEvent::class => 'onProjectBeforeInsert'];
  }
}
