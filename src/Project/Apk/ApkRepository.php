<?php

declare(strict_types=1);

namespace App\Project\Apk;

use App\Storage\FileHelper;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\File;

class ApkRepository
{
  private readonly ?string $dir;

  public function __construct(ParameterBagInterface $parameter_bag)
  {
    $apk_dir = (string) $parameter_bag->get('catrobat.apk.dir');
    FileHelper::verifyDirectoryExists($apk_dir);
    $this->dir = $apk_dir;
  }

  public function save(File $file, string $id): void
  {
    $file->move($this->dir, $this->generateFileNameFromId($id));
  }

  public function remove(string $id): void
  {
    $path = $this->dir.$this->generateFileNameFromId($id);
    if (is_file($path)) {
      unlink($path);
    }
  }

  public function getProjectFile(string $id): File
  {
    return new File($this->dir.$this->generateFileNameFromId($id));
  }

  private function generateFileNameFromId(string $id): string
  {
    return $id.'.apk';
  }
}
