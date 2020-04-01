<?php

namespace App\Catrobat\Services;

use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class TemplateFileRepository extends ProgramFileRepository
{
  public function __construct(string $catrobat_template_storage_dir, string $catrobat_template_storage_path,
                              CatrobatFileCompressor $file_compressor, string $catrobat_upload_temp_dir)
  {
    parent::__construct($catrobat_template_storage_dir, $catrobat_template_storage_path, $file_compressor,
      $catrobat_upload_temp_dir);
  }

  public function deleteTemplateFiles(string $id): void
  {
    try
    {
      $file = $this->getProgramFile($id);
      unlink($file->getPathname());
    }
    catch (FileNotFoundException $fileNotFoundException)
    {
    }
  }
}
