<?php

namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Events\ProgramBeforeInsertEvent;
use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\StatusCode;

class VersionValidator
{
    const MIN_LANGUAGE_VERSION = '0.92';
    const MIN_ANDROID_PROGRAM_VERSION = '0.7.3';
    const MIN_IOS_PROGRAM_VERSION = '0.1';
    const MIN_WINDOWS_PROGRAM_VERSION = '0.1';

    public function onProgramBeforeInsert(ProgramBeforeInsertEvent $event)
    {
        $this->validate($event->getExtractedFile()->getProgramXmlProperties());
    }

    public function validate(\SimpleXMLElement $xml)
    {
        if (version_compare($xml->header->catrobatLanguageVersion, self::MIN_LANGUAGE_VERSION, '<')) {
            throw new InvalidCatrobatFileException('catrobat language version too old', StatusCode::OLD_CATROBAT_LANGUAGE);
        }

        $version = ltrim((string) $xml->header->applicationVersion, 'v');

        switch ($xml->header->platform) {
        case 'Android':
          if (version_compare($version, self::MIN_ANDROID_PROGRAM_VERSION, '<')) {
              throw new InvalidCatrobatFileException('android catrobat version too old', StatusCode::OLD_CATROBAT_VERSION);
          }
          break;

        case 'Windows':
          if (version_compare($xml->header->applicationVersion, self::MIN_WINDOWS_PROGRAM_VERSION, '<')) {
              throw new InvalidCatrobatFileException('windows catrobat version too old', StatusCode::OLD_CATROBAT_VERSION);
          }
          break;

        case 'iOS':
          if (version_compare($xml->header->applicationVersion, self::MIN_IOS_PROGRAM_VERSION, '<')) {
              throw new InvalidCatrobatFileException('ios catrobat version too old', StatusCode::OLD_CATROBAT_VERSION);
          }
          break;
        default:
          throw new InvalidCatrobatFileException('unsupported platform', StatusCode::INTERNAL_SERVER_ERROR);

      }
    }
}
