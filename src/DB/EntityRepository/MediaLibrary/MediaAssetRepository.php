<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\MediaLibrary;

use App\DB\Entity\MediaLibrary\MediaAsset;
use App\DB\Entity\MediaLibrary\MediaCategory;
use App\DB\Entity\MediaLibrary\MediaFileType;
use App\Storage\FileHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @extends ServiceEntityRepository<MediaAsset>
 */
class MediaAssetRepository extends ServiceEntityRepository
{
  private readonly string $dir;

  private readonly string $path;

  private readonly Filesystem $filesystem;

  private readonly string $thumb_dir;

  /**
   * @throws \Exception
   */
  public function __construct(
    ParameterBagInterface $parameter_bag,
    ManagerRegistry $manager_registry,
  ) {
    parent::__construct($manager_registry, MediaAsset::class);

    /** @var string $dir Directory where media asset files are stored */
    $dir = $parameter_bag->get('catrobat.media.dir');

    /** @var string $path Path where files in $dir can be accessed via web */
    $path = $parameter_bag->get('catrobat.media.path');
    $thumb_dir = $dir.'thumbs/';

    if (!is_dir($thumb_dir)) {
      mkdir($thumb_dir, 0777, true);
    }

    FileHelper::verifyDirectoryExists($dir);
    FileHelper::verifyDirectoryExists($thumb_dir);

    $this->dir = $dir;
    $this->path = $path;
    $this->filesystem = new Filesystem();
    $this->thumb_dir = $thumb_dir;
  }

  /**
   * @return array<MediaAsset>
   */
  public function findPaginated(
    int $limit = 20,
    int $offset = 0,
    ?MediaCategory $category = null,
    ?MediaFileType $fileType = null,
    ?string $flavor = null,
    ?string $search = null,
    string $sortBy = 'created_at',
    string $sortOrder = 'DESC',
  ): array {
    $qb = $this->createBaseQueryBuilder($category, $fileType, $flavor, $search);

    // Sorting
    $allowedSortFields = ['name', 'created_at', 'downloads', 'updated_at'];
    if (!in_array($sortBy, $allowedSortFields, true)) {
      $sortBy = 'created_at';
    }
    $sortOrder = 'ASC' === strtoupper($sortOrder) ? 'ASC' : 'DESC';

    $qb->orderBy('a.'.$sortBy, $sortOrder)
      ->addOrderBy('a.id', 'DESC')
      ->setFirstResult($offset)
      ->setMaxResults($limit)
    ;

    return $qb->getQuery()->getResult();
  }

  public function countAll(
    ?MediaCategory $category = null,
    ?MediaFileType $fileType = null,
    ?string $flavor = null,
    ?string $search = null,
  ): int {
    $qb = $this->createBaseQueryBuilder($category, $fileType, $flavor, $search);

    return (int) $qb->select('COUNT(a.id)')
      ->getQuery()
      ->getSingleScalarResult()
    ;
  }

  public function save(MediaAsset $asset): void
  {
    $this->getEntityManager()->persist($asset);
    $this->getEntityManager()->flush();
  }

  public function delete(MediaAsset $asset): void
  {
    $this->getEntityManager()->remove($asset);
    $this->getEntityManager()->flush();
  }

  /**
   * Saves an uploaded file to the media directory and creates a thumbnail if applicable.
   *
   * @throws \ImagickException
   * @throws \ImagickDrawException
   */
  public function saveFile(File $file, string $id, string $extension, bool $create_thumbnail = true): void
  {
    $target = $this->dir.$this->generateFileNameFromId($id, $extension);
    $this->filesystem->copy($file->getPathname(), $target);

    if ($create_thumbnail) {
      $this->createThumbnail($id, $extension);
    }
  }

  /**
   * Removes a file and its thumbnail from disk.
   */
  public function removeFile(string $id, string $extension): void
  {
    $file_name = $this->generateFileNameFromId($id, $extension);
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
   * Creates a thumbnail for an image file.
   *
   * @throws \ImagickException
   * @throws \ImagickDrawException
   */
  public function createThumbnail(string $id, string $extension): void
  {
    $extension = 'catrobat' === $extension ? 'png' : $extension;

    $image_extensions = ['png', 'jpg', 'jpeg', 'gif', 'bmp', 'webp', 'svg'];
    if (!in_array(strtolower($extension), $image_extensions, true)) {
      return; // Only create thumbnails for images
    }

    $source_file = $this->dir.$id.'.'.$extension;
    if (!file_exists($source_file)) {
      return;
    }

    $thumb_file = $this->thumb_dir.$id.'.'.$extension;

    try {
      $real_path = realpath($source_file);
      if (false === $real_path) {
        return;
      }
      $imagick = new \Imagick($real_path);
      $imagick->setImageFormat($extension);
      $imagick->thumbnailImage(300, 300, true);
      $imagick->writeImage($thumb_file);
      $imagick->destroy();
    } catch (\ImagickException $e) {
      // Thumbnail creation failed, but don't throw - asset can still be used
      error_log("Failed to create thumbnail for {$id}.{$extension}: ".$e->getMessage());
    }
  }

  public function getWebPath(string $id, string $extension): string
  {
    $file_path = $this->getFilePath($id, $extension);

    return $this->path.$this->generateFileNameFromId($id, $extension).FileHelper::getTimestampParameter($file_path);
  }

  public function getThumbnailWebPath(string $id, string $extension): string
  {
    $extension = 'catrobat' === $extension ? 'png' : $extension;
    $thumb_path = $this->thumb_dir.$id.'.'.$extension;

    return $this->path.'thumbs/'.$id.'.'.$extension.FileHelper::getTimestampParameter($thumb_path);
  }

  public function getFile(string $id, string $extension): File
  {
    return new File($this->getFilePath($id, $extension));
  }

  public function getFilePath(string $id, string $extension): string
  {
    return $this->dir.$id.'.'.$extension;
  }

  private function generateFileNameFromId(string $id, string $extension): string
  {
    return $id.'.'.$extension;
  }

  private function createBaseQueryBuilder(
    ?MediaCategory $category = null,
    ?MediaFileType $fileType = null,
    ?string $flavor = null,
    ?string $search = null,
  ): QueryBuilder {
    $qb = $this->createQueryBuilder('a')
      ->where('a.active = :active')
      ->setParameter('active', true)
    ;

    if ($category instanceof MediaCategory) {
      $qb->andWhere('a.category = :category')
        ->setParameter('category', $category)
      ;
    }

    if ($fileType instanceof MediaFileType) {
      $qb->andWhere('a.file_type = :file_type')
        ->setParameter('file_type', $fileType)
      ;
    }

    if (null !== $flavor) {
      $qb->join('a.flavors', 'f')
        ->andWhere('f.name = :flavor')
        ->setParameter('flavor', $flavor)
      ;
    }

    if (null !== $search && '' !== trim($search)) {
      $qb->andWhere('a.name LIKE :search OR a.description LIKE :search')
        ->setParameter('search', '%'.$search.'%')
      ;
    }

    return $qb;
  }
}
