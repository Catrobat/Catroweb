<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Events\ProgramBeforeInsertEvent;
use App\Catrobat\Services\ExtractedCatrobatFile;

class LicenseUpdater
{
  /**
   * @var string
   */
  const MEDIA_LICENSE = 'https://developer.catrobat.org/ccbysa_v4';
  /**
   * @var string
   */
  const PROGRAM_LICENSE = 'https://developer.catrobat.org/agpl_v3';

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
