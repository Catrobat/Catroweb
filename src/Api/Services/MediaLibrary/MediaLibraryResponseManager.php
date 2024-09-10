<?php

declare(strict_types=1);

namespace App\Api\Services\MediaLibrary;

use App\Api\Services\Base\AbstractResponseManager;
use App\Api\Services\ResponseCache\ResponseCacheManager;
use App\DB\Entity\MediaLibrary\MediaPackageCategory;
use App\DB\Entity\MediaLibrary\MediaPackageFile;
use App\DB\EntityRepository\MediaLibrary\MediaPackageFileRepository;
use OpenAPI\Server\Model\MediaFileResponse;
use OpenAPI\Server\Service\SerializerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MediaLibraryResponseManager extends AbstractResponseManager
{
  public function __construct(
    TranslatorInterface $translator,
    SerializerInterface $serializer,
    ResponseCacheManager $response_cache_manager,
    private readonly UrlGeneratorInterface $url_generator,
    private readonly ParameterBagInterface $parameter_bag,
    private readonly MediaPackageFileRepository $media_package_file_repository,
  ) {
    parent::__construct($translator, $serializer, $response_cache_manager);
  }

  public function createMediaFilesDataResponse(array $media_package_files, ?string $attributes): array
  {
    $media_files_data_response = [];

    /** @var MediaPackageFile $media_package_file */
    foreach ($media_package_files as $media_package_file) {
      $media_files_data_response[] = $this->createMediaFileResponse($media_package_file, $attributes);
    }

    return $media_files_data_response;
  }

  public function createMediaFileResponse(MediaPackageFile $media_package_file, ?string $attributes): MediaFileResponse
  {
    if (null === $attributes || '' === $attributes || '0' === $attributes) {
      $attributes_list = ['id', 'name'];
    } elseif ('ALL' === $attributes) {
      $attributes_list = ['id', 'name', 'flavors', 'packages', 'category', 'author', 'extension', 'download_url', 'size', 'file_type'];
    } else {
      $attributes_list = explode(',', $attributes);
    }

    $data = [];
    if (in_array('id', $attributes_list, true)) {
      $data['id'] = $media_package_file->getId();
    }

    if (in_array('name', $attributes_list, true)) {
      $data['name'] = $media_package_file->getName();
    }

    if (in_array('flavors', $attributes_list, true)) {
      $data['flavors'] = $media_package_file->getFlavorNames();
    }

    if (in_array('packages', $attributes_list, true)) {
      $data['packages'] = $media_package_file->getCategory()->getPackageNames();
    }

    if (in_array('category', $attributes_list, true)) {
      $data['category'] = $media_package_file->getCategory()->getName();
    }

    if (in_array('author', $attributes_list, true)) {
      $data['author'] = $media_package_file->getAuthor();
    }

    if (in_array('extension', $attributes_list, true)) {
      $data['extension'] = $media_package_file->getExtension();
    }

    if (in_array('download_url', $attributes_list, true)) {
      $data['download_url'] = $this->url_generator->generate(
        'download_media',
        [
          'theme' => $this->parameter_bag->get('umbrellaTheme'),
          'id' => $media_package_file->getId(),
        ],
        UrlGeneratorInterface::ABSOLUTE_URL);
    }

    if (in_array('size', $attributes_list, true)) {
      $file = $this->media_package_file_repository->getMediaFile($media_package_file->getId(), $media_package_file->getExtension());
      $data['size'] = $file->getSize();
    }

    if (in_array('file_type', $attributes_list, true)) {
      $extension = $media_package_file->getExtension();

      $imageExtensions = [
        'bmp', 'cgm', 'g3', 'gif', 'ief', 'jpeg', 'ktx', 'png', 'btif', 'sgi', 'svg', 'tiff', 'psd', 'uvi', 'sub', 'djvu',
        'dwg', 'dxf', 'fbs', 'fpx', 'fst', 'mmr', 'rlc', 'mdi', 'wdp', 'npx', 'wbmp', 'xif', 'webp', '3ds', 'ras', 'cmx',
        'fh', 'ico', 'sid', 'pcx', 'pic', 'pnm', 'pbm', 'pgm', 'ppm', 'rgb', 'tga', 'xbm', 'xpm', 'xwd',
      ];
      $soundExtensions = [
        'adp', 'au', 'mid', 'mp4a', 'mpga', 'oga', 's3m', 'sil', 'uva', 'eol', 'dra', 'dts', 'dtshd', 'lvp', 'pya',
        'ecelp4800', 'ecelp7470', 'ecelp9600', 'rip', 'weba', 'aac', 'aif', 'caf', 'flac', 'mka', 'm3u', 'wax', 'wma',
        'ram', 'rmp', 'wav', 'xm',
      ];
      $videoExtensions = [
        '3gp', '3g2', 'h261', 'h263', 'h264', 'jpgv', 'jpm', 'mj2', 'mp4', 'mpeg', 'ogv', 'qt', 'uvh', 'uvm', 'uvp',
        'uvs', 'uvv', 'dvb', 'fvt', 'mxu', 'pyv', 'uvu', 'viv', 'webm', 'f4v', 'fli', 'flv', 'm4v', 'mkv', 'mng', 'asf',
        'vob', 'wm', 'wmv', 'wmx', 'wvx', 'avi', 'movie', 'smv',
      ];

      if ('catrobat' === $extension) {
        $data['file_type'] = 'project';
      } elseif (in_array($extension, $imageExtensions, true)) {
        $data['file_type'] = 'image';
      } elseif (in_array($extension, $soundExtensions, true)) {
        $data['file_type'] = 'sound';
      } elseif (in_array($extension, $videoExtensions, true)) {
        $data['file_type'] = 'video';
      } else {
        $data['file_type'] = 'other';
      }
    }

    return new MediaFileResponse($data);
  }

  public function createMediaPackageCategoriesResponse(array $media_package_categories, int $limit, int $offset, ?string $attributes): array
  {
    $response_array = [];

    /** @var MediaPackageCategory $media_package_category */
    foreach ($media_package_categories as $media_package_category) {
      $media_package_files = $media_package_category->getFiles();
      if ((0 != $offset && count($media_package_files) <= $offset) || count($response_array) === $limit) {
        if (0 != $offset) {
          $offset -= count($media_package_files);
        }

        continue;
      }

      /** @var MediaPackageFile $media_package_file */
      foreach ($media_package_files as $media_package_file) {
        if (0 != $offset) {
          --$offset;
          continue;
        }

        if (count($response_array) >= $limit) {
          break;
        }

        $response_array[] = $this->createMediaFileResponse($media_package_file, $attributes);
      }
    }

    return $response_array;
  }
}
