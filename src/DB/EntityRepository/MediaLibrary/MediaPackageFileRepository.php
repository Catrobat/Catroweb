<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\MediaLibrary;

use App\DB\Entity\MediaLibrary\MediaPackage;
use App\DB\Entity\MediaLibrary\MediaPackageCategory;
use App\DB\Entity\MediaLibrary\MediaPackageFile;
use App\Storage\FileHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class MediaPackageFileRepository used for interacting with the database when handling MediaPackageFiles.
 */
class MediaPackageFileRepository extends ServiceEntityRepository
{
  private readonly string $dir;
  private readonly string $path;
  private readonly Filesystem $filesystem;
  private readonly string $thumb_dir;

  public function __construct(private readonly ParameterBagInterface $parameter_bag, ManagerRegistry $manager_registry)
  {
    parent::__construct($manager_registry, MediaPackageFile::class);

    /** Directory where media package files are stored */
    $dir = (string) $parameter_bag->get('catrobat.mediapackage.dir');

    /** Path where files in $dir can be accessed via web */
    $path = (string) $parameter_bag->get('catrobat.mediapackage.path');
    $thumb_dir = $dir.'thumbs/';
    if (!is_dir($thumb_dir)) {
      mkdir($thumb_dir);
    }

    FileHelper::verifyDirectoryExists($dir);
    FileHelper::verifyDirectoryExists($thumb_dir);

    $this->dir = $dir;
    $this->path = $path;
    $this->filesystem = new Filesystem();
    $this->thumb_dir = $thumb_dir;
  }

  /**
   * Creates a new MediaPackageFile and saves the specified file to the correct location.
   *
   * @param string               $name     The name
   * @param File                 $file     The file (e.g. png, jpeg)
   * @param MediaPackageCategory $category The MediaPackageCategory this MediaPackageFile should belong to
   * @param iterable             $flavors  The flavors of this MediaPackageFile (e.g. Luna, embroidery)
   * @param string               $author   The author of this MediaPackageFile
   *
   * @return MediaPackageFile the created MediaPackageFile
   *
   * @throws \Exception when an error occurs during creating
   */
  public function createMediaPackageFile(string $name, File $file, MediaPackageCategory $category, iterable $flavors, string $author): MediaPackageFile
  {
    $new_media_package_file = new MediaPackageFile();
    $new_media_package_file->setName($name);
    $new_media_package_file->setFile($file);
    $new_media_package_file->setCategory($category);
    $new_media_package_file->setFlavors($flavors);
    $new_media_package_file->setAuthor($author);
    $new_media_package_file->setExtension($file->getExtension());

    $this->getEntityManager()->persist($new_media_package_file);
    $this->getEntityManager()->flush();

    $this->saveFile($file, $new_media_package_file->getId(), $new_media_package_file->getExtension());

    return $new_media_package_file;
  }

  /**
   * Saves a file, uploaded by the user, to the media package directory
   * and creates a thumbnail, if chosen.
   *
   * @param File   $file             the uploaded file handle
   * @param int    $id               the database id of the file
   * @param string $extension        File extension
   * @param bool   $create_thumbnail Whether a thumbnail should be created or not. Default is true.
   *
   * @throws \ImagickException
   */
  public function moveFile(File $file, int $id, string $extension, bool $create_thumbnail = true): void
  {
    $file->move($this->dir, $this->generateFileNameFromId((string) $id, $extension));
    if ($create_thumbnail) {
      $this->createThumbnail((string) $id, $extension);
    }
  }

  /**
   * Copies a file to the media package directory.
   * Used in test cases.
   *
   * @param File   $file             the source file to copy
   * @param int    $id               the database id of the file
   * @param string $extension        file extension
   * @param bool   $create_thumbnail Whether a thumbnail should be created or not. Default is true.
   *
   * @throws \ImagickException
   */
  public function saveFile(File $file, int $id, string $extension,
    bool $create_thumbnail = true): void
  {
    $target = $this->dir.$this->generateFileNameFromId((string) $id, $extension);
    $this->filesystem->copy($file->getPathname(), $target);
    if ($create_thumbnail) {
      $this->createThumbnail((string) $id, $extension);
    }
  }

  /**
   * Removes a file and its thumbnail from the disk.
   *
   * @param int    $id        the database id of the file
   * @param string $extension File extension
   */
  public function remove(int $id, string $extension): void
  {
    $file_name = $this->generateFileNameFromId((string) $id, $extension);
    $path = $this->dir.$file_name;
    if (is_file($path)) {
      unlink($path);
    }

    $thumb = $this->thumb_dir.$file_name;
    if (is_file($thumb)) {
      unlink($thumb);
    }
  }

  /**
   * Creates missing thumbnails.
   * It checks for files that exist in the base directory but not in the thumbs directory.
   *
   * @throws \ImagickException
   */
  public function createMissingThumbnails(): void
  {
    $finder = new Finder();
    $finder->files()->in($this->dir)->depth(0);

    /** @var \SplFileInfo $file */
    foreach ($finder as $file) {
      $ext = 'catrobat' == $file->getExtension() ? 'png' : $file->getExtension();
      $basename = $file->getBasename('.'.$ext);

      if (!is_file($this->thumb_dir.$basename.'.'.$ext)) {
        $ignored_extensions = ['adp', 'au', 'mid', 'mp4a', 'mpga', 'oga', 's3m', 'sil', 'uva',
          'eol', 'dra', 'dts', 'dtshd', 'lvp', 'pya', 'ecelp4800', 'ecelp7470', 'ecelp9600', 'rip',
          'weba', 'aac', 'aif', 'caf', 'flac', 'mka', 'm3u', 'wax', 'wma', 'ram', 'rmp', 'wav',
          'xm', '3gp', '3g2', 'h261', 'h263', 'h264', 'jpgv', 'jpm', 'mj2', 'mp4', 'mpeg', 'ogv',
          'qt', 'uvh', 'uvm', 'uvp', 'uvs', 'uvv', 'dvb', 'fvt', 'mxu', 'pyv', 'uvu', 'viv',
          'webm', 'f4v', 'fli', 'flv', 'm4v', 'mkv', 'mng', 'asf', 'vob', 'wm', 'wmv', 'wmx',
          'wvx', 'avi', 'movie', 'smv', 'pdf', 'txt', 'rtx', 'zip', '7z', ];
        if (!in_array($file->getExtension(), $ignored_extensions, true)) {
          echo 'Create Thumbnail for '.$file->getFilename().PHP_EOL;
          $this->createThumbnail($basename, $ext);
        }
      }
    }
  }

  /**
   * Returns the web path of a given id and extension.
   *
   * @param int    $id        the database id of the file
   * @param string $extension File extension
   *
   * @return string the web path of a given id and extension
   */
  public function getWebPath(int $id, string $extension): string
  {
    return $this->path.$this->generateFileNameFromId((string) $id, $extension).FileHelper::getTimestampParameter($this->getMediaPath($id, $extension));
  }

  /**
   * Returns the thumbnail web path of a given id and extension.
   *
   * @param int    $id        the database id of the file
   * @param string $extension File extension
   *
   * @return string the thumbnail web path of a given id and extension
   */
  public function getThumbnailWebPath(int $id, string $extension): string
  {
    $extension = 'catrobat' == $extension ? 'png' : $extension;

    return $this->path.'/thumbs/'.$id.'.'.$extension.FileHelper::getTimestampParameter($this->getMediaPath($id, $extension));
  }

  /**
   * Returns a file handle of the media file.
   *
   * @param int    $id        the database id of the file
   * @param string $extension File extension
   */
  public function getMediaFile(int $id, string $extension): File
  {
    return new File($this->getMediaPath($id, $extension));
  }

  /**
   * @param int    $id        the database id of the file
   * @param string $extension File extension
   */
  public function getMediaPath(int $id, string $extension): string
  {
    return $this->dir.$id.'.'.$extension;
  }

  /**
   * Searches the database for MediaPackageFiles containing the mentioned search term in their names.
   *
   * @param string      $term         The search term
   * @param string|null $flavor       If you specify a theme flavor, MediaPackageFiles of that flavor plus all files of the standard flavor 'pocketcode'
   *                                  are returned. If you don't specify a theme flavor, only files of the standard flavor 'pocketcode' are returned.
   * @param string|null $package_name if set, then just MediaPackageFiles belonging to this MediaPackage will be returned
   * @param int|null    $limit        Maximum number of search results that should be returned. Defaults to PHP_INT_MAX.
   * @param int|null    $offset       The starting entry in the search results list. Defaults to 0.
   *
   * @return mixed an array containing the found media files or null if no results found
   */
  public function search(string $term, ?string $flavor = 'pocketcode', ?string $package_name = null, ?int $limit = PHP_INT_MAX, ?int $offset = 0)
  {
    $flavor = $flavor ?: 'pocketcode';

    $qb = $this->createQueryBuilder('f')
      ->where('f.name LIKE :term')
      ->andWhere('f.active = 1')
      ->setParameter('term', '%'.$term.'%')
      ->orderBy('f.name', 'ASC')
      ->setFirstResult($offset)
      ->setMaxResults($limit)
    ;
    $this->addFileFlavorsCondition($qb, $flavor, 'f', true);

    if (null !== $package_name && '' !== trim($package_name)) {
      $qb->join(MediaPackageCategory::class, 'c')
        ->join(MediaPackage::class, 'p')
        ->andWhere('f.category = c')
        ->andWhere('c MEMBER OF p.categories')
        ->andWhere('p.name = :package_name')
        ->setParameter('package_name', $package_name)
      ;
    }

    return $qb->getQuery()->getResult();
  }

  /**
   * Creates a thumbnail for the given id and extension. If a thumb of a .catrobat-file with missing screenshot
   * should be created, this method won't create a thumbnail.
   *
   * @param string $id             the id/name of the file
   * @param string $file_extension File extension
   *
   * @throws \ImagickException|\ImagickDrawException
   */
  private function createThumbnail(string $id, string $file_extension): void
  {
    try {
      $path = $this->dir.$this->generateFileNameFromId($id, $file_extension);
      $imagick = new \Imagick();

      if ('catrobat' == $file_extension) {
        // We are dealing with an media library "object" here. An "object" is basically a .catrobat file containing scenes, characters etc.

        // Searching screenshot in .catrobat file
        $catrobat_archive = new \ZipArchive();
        $catrobat_archive->open($path);

        $screenshot_path_inside_archive = null;
        for ($i = 0; $i < $catrobat_archive->numFiles; ++$i) {
          $filename = $catrobat_archive->getNameIndex($i);
          if (str_contains((string) $filename, 'screenshot.png')) {
            $screenshot_path_inside_archive = $filename;
            break;
          }
        }

        if (null != $screenshot_path_inside_archive) {
          // Getting the screenshot out of the .catrobat file
          $imagick->readImageBlob(file_get_contents('zip://'.$path.'#'.$screenshot_path_inside_archive));
          $thumbnail_extension = 'png'; // The automatic generated screenshots int the .catrobat file are png
        } else {
          // We don't have a screenshot inside the archive, thus we don't have to create a thumb
          return;
        }
      } else {
        $imagick->readImage(realpath($path));
        $thumbnail_extension = $file_extension;
      }

      $meanImg = clone $imagick;
      $meanImg->setBackgroundColor('#ffffff');
      $meanImg->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
      $meanImg->setImageFormat($thumbnail_extension);
      $meanImg->setColorspace(\Imagick::COLORSPACE_GRAY);
      $mean = $meanImg->getImageChannelMean(\Imagick::CHANNEL_GRAY);

      $background = '#ffffff';
      if ($mean['mean'] > 0xD000 && $mean['standardDeviation'] < 2_000) {
        $background = '#888888';
      }

      $imagick->setImageBackgroundColor($background);
      $imagick->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
      $imagick->setImageFormat($thumbnail_extension);
      $imagick->thumbnailImage(200, 0);

      if ('catrobat' == $file_extension) {
        // We want to annotate the thumbnail so that the user can recognize that the thubnail represents an
        // media library "object"

        $draw = new \ImagickDraw();

        // Black text
        $draw->setFillColor('gray');
        $draw->setTextEncoding('UTF-8');

        // Font properties
        $draw->setFont($this->parameter_bag->get('catrobat.mediapackage.font.dir'));
        $draw->setFontSize(50);

        // Create text
        $imagick->annotateImage($draw, 10, 50, 0, '');
      }

      $imagick->writeImage($this->thumb_dir.$id.'.'.$thumbnail_extension);
    } catch (\ImagickException $imagickException) {
      $code = $imagickException->getCode() % 100;
      // for error codes see: https://www.imagemagick.org/script/exception.php
      // allowed: 20 non-images/unknown type; 5 font unavailable (svg etc.)
      if (20 !== $code && 5 !== $code) {
        throw $imagickException;
      }
    }
  }

  /**
   * Generates a file name from given id and extension.
   *
   * @param string $id        the id/name of the file
   * @param string $extension File extension
   *
   * @return string The extension
   */
  private function generateFileNameFromId(string $id, string $extension): string
  {
    return $id.'.'.$extension;
  }

  public function addFileFlavorsCondition(QueryBuilder $query_builder, ?string $flavor = null, string $alias = 'e', bool $include_pocketcode = false): QueryBuilder
  {
    if (null !== $flavor && '' !== trim($flavor)) {
      $where = 'fl.name = :name';
      if ($include_pocketcode) {
        $where .= ' OR fl.name = \'pocketcode\'';
      }
      $query_builder
        ->join($alias.'.flavors', 'fl')
        ->andWhere($where)
        ->setParameter('name', $flavor)
      ;
    }

    return $query_builder;
  }
}
