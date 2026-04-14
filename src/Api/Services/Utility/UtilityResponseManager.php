<?php

declare(strict_types=1);

namespace App\Api\Services\Utility;

use App\Api\Services\Base\AbstractResponseManager;
use App\DB\Entity\FeaturedBanner;
use App\DB\Entity\System\Survey;
use App\Storage\ImageRepository;
use OpenAPI\Server\Model\FeaturedBannerResponse;
use OpenAPI\Server\Model\SurveyResponse;
use OpenAPI\Server\Service\SerializerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UtilityResponseManager extends AbstractResponseManager
{
  public function __construct(
    TranslatorInterface $translator,
    SerializerInterface $serializer,
    CacheItemPoolInterface $cache,
    private readonly ImageRepository $image_repository,
  ) {
    parent::__construct($translator, $serializer, $cache);
  }

  public function createSurveyResponse(Survey $survey): SurveyResponse
  {
    return new SurveyResponse([
      'url' => $survey->getUrl(),
    ]);
  }

  public function createFeaturedBannerResponse(FeaturedBanner $banner): FeaturedBannerResponse
  {
    return new FeaturedBannerResponse([
      'id' => $banner->getId(),
      'type' => $banner->getType(),
      'title' => $banner->getTitle() ?? '',
      'image_url' => $this->resolveImageUrl($banner),
      'link_url' => $this->resolveLinkUrl($banner),
      'priority' => $banner->getPriority(),
    ]);
  }

  private function resolveImageUrl(FeaturedBanner $banner): string
  {
    $image_type = $banner->getImageType();
    $id = $banner->getId();
    if ('' !== $image_type && null !== $id) {
      return '/'.$this->image_repository->getWebPath($id, $image_type, true);
    }

    return match ($banner->getType()) {
      'project' => $this->resolveProjectScreenshot($banner),
      'studio' => $this->resolveStudioCover($banner),
      default => '/images/default/screenshot-card@1x.webp',
    };
  }

  private function resolveProjectScreenshot(FeaturedBanner $banner): string
  {
    $program = $banner->getProgram();
    if (null === $program) {
      return '/images/default/screenshot-card@1x.webp';
    }

    return '/resources/screenshots/'.$program->getId().'/screenshot.png';
  }

  private function resolveStudioCover(FeaturedBanner $banner): string
  {
    $studio = $banner->getStudio();
    if (null === $studio) {
      return '/images/default/screenshot-card@1x.webp';
    }

    $cover = $studio->getCoverAssetPath();

    return null !== $cover && '' !== $cover ? '/'.$cover : '/images/default/screenshot-card@1x.webp';
  }

  private function resolveLinkUrl(FeaturedBanner $banner): ?string
  {
    return match ($banner->getType()) {
      'project' => $this->resolveProjectLinkUrl($banner),
      'studio' => $this->resolveStudioLinkUrl($banner),
      'link' => $banner->getUrl(),
      default => null,
    };
  }

  private function resolveProjectLinkUrl(FeaturedBanner $banner): ?string
  {
    $program = $banner->getProgram();

    return null !== $program ? '/app/project/'.$program->getId() : null;
  }

  private function resolveStudioLinkUrl(FeaturedBanner $banner): ?string
  {
    $studio = $banner->getStudio();

    return null !== $studio ? '/app/studio/'.$studio->getId() : null;
  }
}
