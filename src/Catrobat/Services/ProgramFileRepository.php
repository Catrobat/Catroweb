<?php

namespace App\Catrobat\Services;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use App\Catrobat\Exceptions\InvalidStorageDirectoryException;


/**
 * Class ProgramFileRepository
 * @package App\Catrobat\Services
 */
class ProgramFileRepository
{
  /**
   * @var
   */
  protected $directory;
  /**
   * @var Filesystem
   */
  private $filesystem;
  /**
   * @var
   */
  protected $webpath;
  /**
   * @var CatrobatFileCompressor
   */
  private $file_compressor;

  /**
   * @var
   */
  private $tmp_dir;

  /**
   * ProgramFileRepository constructor.
   *
   * @param $catrobat_file_storage_dir
   * @param $catrobat_file_storage_path
   * @param CatrobatFileCompressor $file_compressor
   * @param $catrobat_upload_temp_dir
   */
  public function __construct($catrobat_file_storage_dir, $catrobat_file_storage_path,
                              CatrobatFileCompressor $file_compressor, $catrobat_upload_temp_dir)
  {
    $directory = $catrobat_file_storage_dir;
    $tmp_dir = $catrobat_upload_temp_dir;

    if (!is_dir($directory))
    {
      throw new InvalidStorageDirectoryException($directory . ' is not a valid directory');
    }

    if ($tmp_dir && !is_dir($tmp_dir))
    {
      throw new InvalidStorageDirectoryException($tmp_dir . ' is not a valid directory');
    }

    $this->directory = $directory;
    $this->webpath = $catrobat_file_storage_path;
    $this->tmp_dir = $tmp_dir;
    $this->filesystem = new Filesystem();
    $this->file_compressor = $file_compressor;
  }

  /**
   * @param ExtractedCatrobatFile $extracted
   * @param                       $id
   */
  public function saveProgram(ExtractedCatrobatFile $extracted, $id)
  {
    $this->file_compressor->compress($extracted->getPath(), $this->directory, $id);
  }

  /**
   * @param ExtractedCatrobatFile $extracted
   * @param                       $id
   */
  public function saveProgramTemp(ExtractedCatrobatFile $extracted, $id)
  {
    if ($this->tmp_dir)
    {
      $this->file_compressor->compress($extracted->getPath(), $this->tmp_dir, $id);
    }
  }

  /**
   * @param $id
   */
  public function makeTempProgramPerm($id)
  {
    if ($this->tmp_dir)
    {
      $this->filesystem->copy($this->tmp_dir . $id . ".catrobat", $this->directory . $id . ".catrobat", true);
      $this->filesystem->remove($this->tmp_dir . $id . ".catrobat");
    }
  }

  /**
   * @param $id
   */
  public function deleteProgramFile($id)
  {
    $this->filesystem->remove($this->directory . $id . ".catrobat");
  }

  /**
   * @param File $file
   * @param      $id
   */
  public function saveProgramfile(File $file, $id)
  {
    $this->filesystem->copy($file->getPathname(), $this->directory . $id . '.catrobat');
  }

  /**
   * @param $id
   *
   * @return File
   */
  public function getProgramFile($id)
  {
    return new File($this->directory . $id . '.catrobat');
  }
}
