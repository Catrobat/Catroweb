<?php

namespace Catrobat\AppBundle\Services;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Catrobat\AppBundle\Exceptions\InvalidStorageDirectoryException;

class ProgramFileRepository
{
    private $directory;
    private $filesystem;
    private $webpath;
    private $file_compressor;

    public function __construct($directory, $webpath, CatrobatFileCompressor $file_compressor)
    {
        if (!is_dir($directory)) {
            throw new InvalidStorageDirectoryException($directory.' is not a valid directory');
        }
        $this->directory = $directory;
        $this->webpath = $webpath;
        $this->filesystem = new Filesystem();
        $this->file_compressor = $file_compressor;
    }

    public function saveProgram(ExtractedCatrobatFile $extracted, $id)
    {
        $this->file_compressor->compress($extracted->getPath(), $this->directory, $id);
    }

    public function saveProgramfile(File $file, $id)
    {
        $this->filesystem->copy($file->getPathname(), $this->directory.$id.'.catrobat');
    }

    public function getProgramFile($id)
    {
        return new File($this->directory.$id.'.catrobat');
    }
}
