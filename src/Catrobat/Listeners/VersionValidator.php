<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Events\ProgramBeforeInsertEvent;
use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\StatusCode;
use App\Catrobat\Exceptions\Upload\OldCatrobatLanguageVersionException;
use App\Catrobat\Exceptions\Upload\OldApplicationVersionException;

/**
 * Class VersionValidator
 * @package App\Catrobat\Listeners
 */
class VersionValidator
{
  const MIN_LANGUAGE_VERSION = '0.92';
  const MIN_ANDROID_PROGRAM_VERSION = '0.7.3';
  const MIN_IOS_PROGRAM_VERSION = '0.1';
  const MIN_WINDOWS_PROGRAM_VERSION = '0.1';

  /**
   * @param ProgramBeforeInsertEvent $event
   */
  public function onProgramBeforeInsert(ProgramBeforeInsertEvent $event)
  {
    $this->validate($event->getExtractedFile()->getProgramXmlProperties());
  }

  /**
   * @param \SimpleXMLElement $xml
   */
  public function validate(\SimpleXMLElement $xml)
  {
    if (version_compare($xml->header->catrobatLanguageVersion, self::MIN_LANGUAGE_VERSION, '<'))
    {
      throw new OldCatrobatLanguageVersionException();
    }

    $version = ltrim((string)$xml->header->applicationVersion, 'v');

    switch ($xml->header->platform)
    {
      case 'Android':
        if (version_compare($version, self::MIN_ANDROID_PROGRAM_VERSION, '<'))
        {
          throw new OldApplicationVersionException('android catrobat version too old');
        }
        break;

      case 'Windows':
        if (version_compare($xml->header->applicationVersion, self::MIN_WINDOWS_PROGRAM_VERSION, '<'))
        {
          throw new OldApplicationVersionException('windows catrobat version too old');
        }
        break;

      case 'iOS':
        if (version_compare($xml->header->applicationVersion, self::MIN_IOS_PROGRAM_VERSION, '<'))
        {
          throw new OldApplicationVersionException('ios catrobat version too old');
        }
        break;
      default:
        throw new InvalidCatrobatFileException('unsupported platform', StatusCode::INTERNAL_SERVER_ERROR);

    }
  }
}
