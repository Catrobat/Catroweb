<?php

declare(strict_types=1);

namespace App\Project\CatrobatFile;

use App\Project\Event\ProjectBeforeInsertEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LicenseUpdaterEventSubscriber implements EventSubscriberInterface
{
  /**
   * @var string
   */
  final public const MEDIA_LICENSE = 'https://developer.catrobat.org/ccbysa_v4';
  /**
   * @var string
   */
  final public const PROJECT_LICENSE = 'https://developer.catrobat.org/agpl_v3';

  public function onProjectBeforeInsert(ProjectBeforeInsertEvent $event): void
  {
    $this->update($event->getExtractedFile());
  }

  /**
   * @psalm-suppress UndefinedPropertyAssignment
   */
  public function update(ExtractedCatrobatFile $file): void
  {
    $project_xml_properties = $file->getProjectXmlProperties();
    $project_xml_properties->header->mediaLicense = self::MEDIA_LICENSE;
    $project_xml_properties->header->programLicense = self::PROJECT_LICENSE;
    $file->saveProjectXmlProperties();
  }

  public static function getSubscribedEvents(): array
  {
    return [ProjectBeforeInsertEvent::class => ['onProjectBeforeInsert', -1]];
  }
}
