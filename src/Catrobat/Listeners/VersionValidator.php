<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Events\ProgramBeforeInsertEvent;
use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\Exceptions\Upload\OldApplicationVersionException;
use App\Catrobat\Exceptions\Upload\OldCatrobatLanguageVersionException;
use SimpleXMLElement;
use Symfony\Component\HttpFoundation\Response;

class VersionValidator
{
  /**
   * @var string
   */
  const MIN_LANGUAGE_VERSION = '0.92';
  /**
   * @var string
   */
  const MIN_ANDROID_PROGRAM_VERSION = '0.7.3';
  /**
   * @var string
   */
  const MIN_IOS_PROGRAM_VERSION = '0.1';
  /**
   * @var string
   */
  const MIN_WINDOWS_PROGRAM_VERSION = '0.1';

  public function onProgramBeforeInsert(ProgramBeforeInsertEvent $event): void
  {
    $this->validate($event->getExtractedFile()->getProgramXmlProperties());
  }

  public function validate(SimpleXMLElement $xml): void
  {
    if (version_compare($xml->header->catrobatLanguageVersion, self::MIN_LANGUAGE_VERSION, '<'))
    {
      throw new OldCatrobatLanguageVersionException();
    }

    $version = ltrim((string) $xml->header->applicationVersion, 'v');

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
        throw new InvalidCatrobatFileException('unsupported platform', Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }
}
