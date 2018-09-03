<?php


namespace Catrobat\AppBundle\Services;

use Catrobat\AppBundle\Services\ProgramFileRepository;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class TemplateFileRepository extends ProgramFileRepository
{
  public function __construct($directory, $webpath, CatrobatFileCompressor $file_compressor)
  {
    parent::__construct($directory, $webpath, $file_compressor);
  }

  public function deleteTemplateFiles($id)
  {
    try
    {
      $file = $this->getProgramFile($id);
      unlink($file->getPathname());
    } catch (FileNotFoundException $e)
    {
    }
  }
}