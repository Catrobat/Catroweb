<?php

declare(strict_types=1);

namespace App\Api_deprecated\Controller;

use App\Api\Services\Base\TranslatorAwareInterface;
use App\Api\Services\Base\TranslatorAwareTrait;
use App\DB\Entity\MediaLibrary\MediaPackage;
use App\DB\Entity\MediaLibrary\MediaPackageCategory;
use App\DB\Entity\MediaLibrary\MediaPackageFile;
use App\DB\EntityRepository\MediaLibrary\MediaPackageFileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @deprecated
 */
class MediaPackageController extends AbstractController implements TranslatorAwareInterface
{
  use TranslatorAwareTrait;

  public function __construct(
    TranslatorInterface $translator,
    private readonly EntityManagerInterface $entity_manager,
    protected MediaPackageFileRepository $media_package_file_repository
  ) {
    $this->initTranslator($translator);
  }

  /**
   * @deprecated
   */
  #[Route(path: '/api/media/package/{package}/json', name: 'api_media_lib_package', requirements: ['package' => '\w+'], defaults: ['_format' => 'json'], methods: ['GET'])]
  public function getMediaFilesForPackage(string $package): JsonResponse
  {
    /** @var MediaPackage|null $media_package */
    $media_package = $this->entity_manager->getRepository(MediaPackage::class)
      ->findOneBy(['name' => $package])
    ;
    if (null === $media_package) {
      return new JsonResponse(
        ['statusCode' => 523,
          'message' => $package.' not found', ]
      );
    }
    $json_response_array = [];
    $media_package_categories = $media_package->getCategories();
    if ($media_package_categories->isEmpty()) {
      return new JsonResponse(
        $json_response_array
      );
    }
    /** @var MediaPackageCategory $media_package_category */
    foreach ($media_package_categories as $media_package_category) {
      $media_package_files = $media_package_category->getFiles();
      if (!$media_package_files->isEmpty()) {
        /** @var MediaPackageFile $media_package_file */
        foreach ($media_package_files as $media_package_file) {
          $json_response_array[] = $this->createArrayOfMediaData($media_package_file);
        }
      }
    }

    return new JsonResponse(
      $json_response_array
    );
  }

  /**
   * @deprecated
   */
  #[Route(path: '/api/media/packageByNameUrl/package', name: 'api_media_lib_package_bynameurl')]
  #[Route(path: '/api/media/packageByNameUrl/{package}/json', name: 'api_media_lib_package_bynameurl_old', requirements: ['package' => '\w+'], defaults: ['_format' => 'json'], methods: ['GET'])]
  public function getMediaFilesForPackageByNameUrl(Request $request, string $package = ''): JsonResponse
  {
    if (!$package) {
      $package = strval($request->query->get('package'));
    }
    /** @var MediaPackage|null $media_package */
    $media_package = $this->entity_manager->getRepository(MediaPackage::class)
      ->findOneBy(['nameUrl' => $package])
    ;
    if (null === $media_package) {
      return new JsonResponse(
        ['statusCode' => 523,
          'message' => $package.' not found', ]
      );
    }
    $json_response_array = [];
    $media_package_categories = $media_package->getCategories();
    if ($media_package_categories->isEmpty()) {
      return new JsonResponse(
        $json_response_array
      );
    }
    foreach ($media_package_categories as $media_package_category) {
      /** @var MediaPackageFile[]|null $media_package_files */
      $media_package_files = $media_package_category->getFiles();
      if (!empty($media_package_files)) {
        foreach ($media_package_files as $media_package_file) {
          $json_response_array[] = $this->createArrayOfMediaData($media_package_file);
        }
      }
    }

    return new JsonResponse(
      $json_response_array
    );
  }

  protected function createArrayOfMediaData(MediaPackageFile $media_package_file): array
  {
    $id = $media_package_file->getId();
    $name = $media_package_file->getName();
    $flavor = $media_package_file->getFlavor();
    $package = $media_package_file->getCategory()->getPackage()->first()->getName();
    $category = $media_package_file->getCategory()->getName();
    $author = $media_package_file->getAuthor();
    $extension = $media_package_file->getExtension();
    $download_url = $this->generateUrl('download_media', [
      'id' => $id,
    ]);

    $file = $this->media_package_file_repository->getMediaFile($id, $extension);
    $size = $this->humanFileSize($file->getSize());
    if ($this->isFileACatrobatFile($extension)) {
      $fileType = 'catrobat';
      $description_line1 = $this->trans('media_library.file.type_description.project');
    } elseif ($this->isFileAnImageFile($extension)) {
      $fileType = 'image';
      $description_line1 = $this->trans('media_library.file.type_description.image');
    } elseif ($this->isFileASoundFile($extension)) {
      $fileType = 'sound';
      $description_line1 = $this->trans('media_library.file.type_description.sound');
    } elseif ($this->isFileAVideoFile($extension)) {
      $fileType = 'video';
      $description_line1 = $this->trans('media_library.file.type_description.video');
    } else {
      $fileType = 'unknown';
      $description_line1 = $this->trans('media_library.file.type_description.default');
    }
    $description_line2 = $this->trans('media_library.file.size', ['%size%' => $size]);
    $description = $description_line1.'</br>'.$description_line2;

    $url = $media_package_file->getUrl();
    $project_url = null;
    if (!empty($url)) {
      $project_id = $url;
      $project_url = $this->generateUrl('program', [
        'id' => $project_id,
      ]);
    }

    return
      [
        'id' => $id,
        'name' => $name,
        'flavor' => $flavor,
        'package' => $package,
        'category' => $category,
        'author' => $author,
        'extension' => $extension,
        'project_url' => $project_url,
        'download_url' => $download_url,
        'type' => $fileType,
        'description' => $description,
      ];
  }

  protected function isFileACatrobatFile(string $extension): bool
  {
    return 'catrobat' === $extension;
  }

  protected function isFileASoundFile(string $extension): bool
  {
    $soundExtensions = [
      'adp', 'au', 'mid', 'mp4a', 'mpga', 'oga', 's3m', 'sil', 'uva', 'eol', 'dra', 'dts', 'dtshd', 'lvp', 'pya',
      'ecelp4800', 'ecelp7470', 'ecelp9600', 'rip', 'weba', 'aac', 'aif', 'caf', 'flac', 'mka', 'm3u', 'wax', 'wma',
      'ram', 'rmp', 'wav', 'xm',
    ];

    return in_array($extension, $soundExtensions, true);
  }

  protected function isFileAnImageFile(string $extension): bool
  {
    $imageExtensions = [
      'bmp', 'cgm', 'g3', 'gif', 'ief', 'jpeg', 'ktx', 'png', 'btif', 'sgi', 'svg', 'tiff', 'psd', 'uvi', 'sub', 'djvu',
      'dwg', 'dxf', 'fbs', 'fpx', 'fst', 'mmr', 'rlc', 'mdi', 'wdp', 'npx', 'wbmp', 'xif', 'webp', '3ds', 'ras', 'cmx',
      'fh', 'ico', 'sid', 'pcx', 'pic', 'pnm', 'pbm', 'pgm', 'ppm', 'rgb', 'tga', 'xbm', 'xpm', 'xwd',
    ];

    return in_array($extension, $imageExtensions, true);
  }

  protected function isFileAVideoFile(string $extension): bool
  {
    $videoExtensions = [
      '3gp', '3g2', 'h261', 'h263', 'h264', 'jpgv', 'jpm', 'mj2', 'mp4', 'mpeg', 'ogv', 'qt', 'uvh', 'uvm', 'uvp',
      'uvs', 'uvv', 'dvb', 'fvt', 'mxu', 'pyv', 'uvu', 'viv', 'webm', 'f4v', 'fli', 'flv', 'm4v', 'mkv', 'mng', 'asf',
      'vob', 'wm', 'wmv', 'wmx', 'wvx', 'avi', 'movie', 'smv',
    ];

    return in_array($extension, $videoExtensions, true);
  }

  protected function humanFileSize(int $size): string
  {
    return sprintf('%.2f', $size / 1_048_576).'MB';
  }
}
