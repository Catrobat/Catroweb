<?php

namespace App\Project\CatrobatFile;

use App\Project\Event\ProgramBeforeInsertEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;

class VersionValidatorEventSubscriber implements EventSubscriberInterface
{
  /**
   * @var string
   */
  final public const MIN_LANGUAGE_VERSION = '0.92';
  /**
   * @var string
   */
  final public const MIN_ANDROID_PROGRAM_VERSION = '0.7.3';
  /**
   * @var string
   */
  final public const MIN_IOS_PROGRAM_VERSION = '0.1';
  /**
   * @var string
   */
  final public const MIN_WINDOWS_PROGRAM_VERSION = '0.1';

  public function onProgramBeforeInsert(ProgramBeforeInsertEvent $event): void
  {
    $this->validate($event->getExtractedFile()->getProgramXmlProperties());
  }

  public function validate(\SimpleXMLElement $xml): void
  {
    if (version_compare($xml->header->catrobatLanguageVersion, self::MIN_LANGUAGE_VERSION, '<')) {
      throw new InvalidCatrobatFileException('errors.languageversion.tooold', 518);
    }

    $version = ltrim((string) $xml->header->applicationVersion, 'v');

    switch ($xml->header->platform) {
      case 'Android':
        if (version_compare($version, self::MIN_ANDROID_PROGRAM_VERSION, '<')) {
          throw new InvalidCatrobatFileException('errors.programversion.tooold', 519, 'android catrobat version too old');
        }
        break;

      case 'Windows':
        if (version_compare($xml->header->applicationVersion, self::MIN_WINDOWS_PROGRAM_VERSION, '<')) {
          throw new InvalidCatrobatFileException('errors.programversion.tooold', 519, 'windows catrobat version too old');
        }
        break;

      case 'iOS':
        if (version_compare($xml->header->applicationVersion, self::MIN_IOS_PROGRAM_VERSION, '<')) {
          throw new InvalidCatrobatFileException('errors.programversion.tooold', 519, 'ios catrobat version too old');
        }
        break;
      default:
        throw new InvalidCatrobatFileException('unsupported platform', Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array
  {
    return [ProgramBeforeInsertEvent::class => 'onProgramBeforeInsert'];
  }
}
