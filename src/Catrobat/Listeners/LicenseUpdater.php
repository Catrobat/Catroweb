<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Events\ProgramBeforeInsertEvent;
use App\Catrobat\Services\ExtractedCatrobatFile;

/**
 * Class LicenseUpdater.
 */
class LicenseUpdater
{
  const MEDIALICENSE = 'https://developer.catrobat.org/ccbysa_v4';
  const PROGRAMLICENSE = 'https://developer.catrobat.org/agpl_v3';

  public function onProgramBeforeInsert(ProgramBeforeInsertEvent $event)
  {
    $this->update($event->getExtractedFile());
  }

  public function update(ExtractedCatrobatFile $file)
  {
    $program_xml_properties = $file->getProgramXmlProperties();
    $program_xml_properties->header->mediaLicense = self::MEDIALICENSE;
    $program_xml_properties->header->programLicense = self::PROGRAMLICENSE;
    $file->saveProgramXmlProperties();
  }
}
