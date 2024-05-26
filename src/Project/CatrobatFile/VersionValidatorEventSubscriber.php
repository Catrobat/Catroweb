<?php

declare(strict_types=1);

namespace App\Project\CatrobatFile;

use App\Project\Event\ProjectBeforeInsertEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;

class VersionValidatorEventSubscriber implements EventSubscriberInterface
{
  final public const string MIN_LANGUAGE_VERSION = '0.92';

  final public const string MIN_ANDROID_PROGRAM_VERSION = '0.7.3';

  final public const string MIN_IOS_PROGRAM_VERSION = '0.1';

  final public const string MIN_WINDOWS_PROGRAM_VERSION = '0.1';

  public function onProjectBeforeInsert(ProjectBeforeInsertEvent $event): void
  {
    $this->validate($event->getExtractedFile()->getProjectXmlProperties());
  }

  public function validate(\SimpleXMLElement $xml): void
  {
    /* @psalm-suppress InvalidPropertyFetch */
    if (version_compare((string) $xml->header->catrobatLanguageVersion, self::MIN_LANGUAGE_VERSION, '<')) {
      throw new InvalidCatrobatFileException('errors.languageversion.tooold', 518);
    }

    $version = ltrim((string) $xml->header->applicationVersion, 'v');

    switch ((string) $xml->header->platform) {
      case 'Android':
        if (version_compare($version, self::MIN_ANDROID_PROGRAM_VERSION, '<')) {
          throw new InvalidCatrobatFileException('errors.projectversion.tooold', 519, 'android catrobat version too old');
        }

        break;

      case 'Windows':
        if (version_compare((string) $xml->header->applicationVersion, self::MIN_WINDOWS_PROGRAM_VERSION, '<')) {
          throw new InvalidCatrobatFileException('errors.projectversion.tooold', 519, 'windows catrobat version too old');
        }

        break;

      case 'iOS':
        if (version_compare((string) $xml->header->applicationVersion, self::MIN_IOS_PROGRAM_VERSION, '<')) {
          throw new InvalidCatrobatFileException('errors.projectversion.tooold', 519, 'ios catrobat version too old');
        }

        break;
      default:
        throw new InvalidCatrobatFileException('unsupported platform', Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  #[\Override]
  public static function getSubscribedEvents(): array
  {
    return [ProjectBeforeInsertEvent::class => 'onProjectBeforeInsert'];
  }
}
