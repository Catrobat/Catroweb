<?php

namespace App\Project\CatrobatFile;

use App\Project\Event\ProgramBeforeInsertEvent;

class LicenseUpdater
{
  /**
   * @var string
   */
  public const MEDIA_LICENSE = 'https://developer.catrobat.org/ccbysa_v4';
  /**
   * @var string
   */
  public const PROGRAM_LICENSE = 'https://developer.catrobat.org/agpl_v3';

  public function onProgramBeforeInsert(ProgramBeforeInsertEvent $event): void
  {
    $this->update($event->getExtractedFile());
  }

  public function update(ExtractedCatrobatFile $file): void
  {
    $program_xml_properties = $file->getProgramXmlProperties();
    $program_xml_properties->header->mediaLicense = self::MEDIA_LICENSE;
    $program_xml_properties->header->programLicense = self::PROGRAM_LICENSE;
    $file->saveProgramXmlProperties();
  }
}
