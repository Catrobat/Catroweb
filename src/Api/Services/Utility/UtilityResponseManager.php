<?php

declare(strict_types=1);

namespace App\Api\Services\Utility;

use App\Api\Services\Base\AbstractResponseManager;
use App\DB\Entity\FeaturedBanner;
use App\DB\Entity\System\Survey;
use App\Project\ProjectManager;
use App\Storage\ImageRepository;
use App\Studio\StudioManager;
use OpenAPI\Server\Model\FeaturedBannerResponse;
use OpenAPI\Server\Model\ImageVariants;
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
    private readonly StudioManager $studio_manager,
    private readonly ProjectManager $project_manager,
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
      'image_variants' => $this->getFeaturedBannerVariants($banner),
      'link_url' => $this->resolveLinkUrl($banner),
      'video_url' => $banner->getVideoUrl(),
      'priority' => $banner->getPriority(),
    ]);
  }

  public function getFeaturedBannerVariants(FeaturedBanner $banner): ?ImageVariants
  {
    $id = $banner->getId();
    if (null === $id) {
      return null;
    }

    // Own uploaded/generated variants first
    $variants = $this->image_repository->getFeaturedVariants($id);
    if (null !== $variants) {
      return $variants;
    }

    // Fall back to linked content's variants
    return match ($banner->getType()) {
      'project' => $this->resolveProjectVariants($banner),
      'studio' => $this->resolveStudioVariants($banner),
      default => null,
    };
  }

  private function resolveProjectVariants(FeaturedBanner $banner): ?ImageVariants
  {
    $program = $banner->getProgram();

    $id = $program?->getId();

    return null !== $id ? $this->project_manager->getScreenshotVariants($id) : null;
  }

  private function resolveStudioVariants(FeaturedBanner $banner): ?ImageVariants
  {
    return $this->studio_manager->getCoverVariants($banner->getStudio());
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
