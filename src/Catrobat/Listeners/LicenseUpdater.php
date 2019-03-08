<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Events\ProgramBeforeInsertEvent;
use App\Catrobat\Services\ExtractedCatrobatFile;

/**
 * Class LicenseUpdater
 * @package App\Catrobat\Listeners
 */
class LicenseUpdater
{
  const MEDIALICENSE = 'http://developer.catrobat.org/ccbysa_v4';
  const PROGRAMLICENSE = 'http://developer.catrobat.org/agpl_v3';

  /**
   * @param ProgramBeforeInsertEvent $event
   */
  public function onProgramBeforeInsert(ProgramBeforeInsertEvent $event)
  {
    $this->update($event->getExtractedFile());
  }

  /**
   * @param ExtractedCatrobatFile $file
   */
  public function update(ExtractedCatrobatFile $file)
  {
    $program_xml_properties = $file->getProgramXmlProperties();
    $program_xml_properties->header->mediaLicense = self::MEDIALICENSE;
    $program_xml_properties->header->programLicense = self::PROGRAMLICENSE;
    $file->saveProgramXmlProperties();
  }
}
