<?php

namespace App\Project\CatrobatFile;

use App\Project\Event\ProgramBeforeInsertEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Finder\Finder;

class OnlyDefinedImagesValidatorEventSubscriber implements EventSubscriberInterface
{
  public function onProgramBeforeInsert(ProgramBeforeInsertEvent $event): void
  {
    $this->validate($event->getExtractedFile());
  }

  public function validate(ExtractedCatrobatFile $file): void
  {
    $files_in_xml = self::getImagesFromXml($file->getProgramXmlProperties());
    $files_in_directory = self::getImagesFromImageDirectory($file->getPath());

    $files = array_diff($files_in_directory, $files_in_xml);
    if (count($files) > 0) {
      throw new InvalidCatrobatFileException('Unexpected files: ['.implode(', ', $files).']', 525);
    }
    $files = array_diff($files_in_xml, $files_in_directory);
    if (count($files) > 0) {
      throw new InvalidCatrobatFileException('errors.image.missing', 524, 'Missing image: '.implode(', ', $files).']');
    }
  }

  /**
   * @param mixed $base_path
   */
  protected static function getImagesFromImageDirectory($base_path): array
  {
    $images = [];
    $finder = new Finder();
    $finder->notPath('/^.nomedia$/')->ignoreDotFiles(false)->ignoreVCS(false)->in($base_path.'/images/');
    foreach ($finder as $file) {
      $images[] = $file->getRelativePathname();
    }

    return $images;
  }

  /**
   * @param mixed $xml
   */
  protected static function getImagesFromXml($xml): array
  {
    $defined_file_nodes = $xml->xpath('/program/objectList/object/lookList/look/fileName');
    $defined_files = $defined_file_nodes;

    return array_unique($defined_files);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array
  {
    return [ProgramBeforeInsertEvent::class => 'onProgramBeforeInsert'];
  }
}
