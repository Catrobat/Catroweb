<?php

namespace App\Project\CatrobatFile;

use App\Project\Event\ProgramBeforeInsertEvent;
use Symfony\Component\Finder\Finder;

class FileStructureValidator
{
  public function onProgramBeforeInsert(ProgramBeforeInsertEvent $event): void
  {
    $this->validate($event->getExtractedFile());
  }

  public function validate(ExtractedCatrobatFile $file): void
  {
    $finder = new Finder();
    $finder->in($file->getPath())
      ->name('*/*')
      ->exclude(['/^.*/sounds/', '/^.*/images/'])
      ->notPath('/^code.xml$/')
      ->notPath('/^permissions.txt$/')
      ->notPath('/^screenshot.png$/')
      ->notPath('/^manual_screenshot.png$/')
      ->notPath('/^automatic_screenshot.png$/')
    ;

    $test = '';
    foreach ($finder as $file1) {
      $test .= $file1->getRelativePathname()."\n";
      file_put_contents('/home/catroweb/Catroweb/FileStructureValidator_error.log', $test);
    }

    if ($finder->count() > 0) {
      $list = [];
      foreach ($finder as $file) {
        $list[] = $file->getRelativePathname();
      }

      print_r($list);

      throw new InvalidCatrobatFileException('errors.file.unexpected', 525, 'unexpected files found: '.implode(', ', $list));
    }
  }
}
