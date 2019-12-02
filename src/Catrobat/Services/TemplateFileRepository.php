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
   * @param $catrobat_template_storage_dir
   * @param $catrobat_template_storage_path
   * @param CatrobatFileCompressor $file_compressor
   * @param $catrobat_upload_temp_dir
   */
  public function __construct($catrobat_template_storage_dir, $catrobat_template_storage_path,
                              CatrobatFileCompressor $file_compressor, $catrobat_upload_temp_dir)
  {
    parent::__construct($catrobat_template_storage_dir, $catrobat_template_storage_path, $file_compressor,
      $catrobat_upload_temp_dir);
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