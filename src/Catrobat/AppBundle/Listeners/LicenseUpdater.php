<?php

namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Model\ExtractedCatrobatFile;

class LicenseUpdater
{
    const MEDIALICENSE = "http://developer.catrobat.org/ccbysa_v4";

    public function update(ExtractedCatrobatFile $file)
    {
        $program_xml_properties = $file->getProgramXmlProperties();
        $program_xml_properties->header->mediaLicense = self::MEDIALICENSE;
    }
}
