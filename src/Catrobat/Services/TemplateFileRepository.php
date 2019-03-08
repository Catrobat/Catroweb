<?php


namespace App\Catrobat\Services;

use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

/**
 * Class TemplateFileRepository
 * @package App\Catrobat\Services
 */
class TemplateFileRepository extends ProgramFileRepository
{
  /**
   * TemplateFileRepository constructor.
   *
   * @param                        $directory
   * @param                        $webpath
   * @param CatrobatFileCompressor $file_compressor
   * @param                        $tmp_dir
   */
  public function __construct($directory, $webpath, CatrobatFileCompressor $file_compressor, $tmp_dir)
  {
    parent::__construct($directory, $webpath, $file_compressor, $tmp_dir);
  }

  /**
   * @param $id
   */
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