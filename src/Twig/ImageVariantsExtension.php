<?php

declare(strict_types=1);

namespace App\Twig;

use App\DB\Entity\Studio\Studio;
use App\DB\Entity\User\User;
use App\Project\ProjectManager;
use App\Storage\Images\ImageVariantUrlBuilder;
use App\Studio\StudioManager;
use App\User\UserAvatarService;
use OpenAPI\Server\Model\ImageVariants;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Exposes the shared AVIF/WebP variant builders to Twig so server-rendered
 * pages can emit the same responsive `<picture>` markup the API returns.
 */
class ImageVariantsExtension extends AbstractExtension
{
  public function __construct(
    private readonly ImageVariantUrlBuilder $url_builder,
    private readonly StudioManager $studio_manager,
    private readonly UserAvatarService $user_avatar_service,
    private readonly ProjectManager $project_manager,
  ) {
  }

  #[\Override]
  public function getFunctions(): array
  {
    return [
      new TwigFunction('studio_cover_variants', $this->studioCoverVariants(...)),
      new TwigFunction('user_avatar_variants', $this->userAvatarVariants(...)),
      new TwigFunction('project_screenshot_variants', $this->projectScreenshotVariants(...)),
    ];
  }

  public function studioCoverVariants(?Studio $studio): ?ImageVariants
  {
    if (!$studio instanceof Studio) {
      return null;
    }

    $key = $studio->getCoverAssetPath();
    if (null === $key || '' === $key) {
      return null;
    }

    return $this->url_builder->build(
      $this->studio_manager->getStudioCoverDir(),
      $this->studio_manager->getStudioCoverPublicPath(),
      $key,
    );
  }

  public function userAvatarVariants(?User $user): ?ImageVariants
  {
    return $this->user_avatar_service->getVariants($user);
  }

  public function projectScreenshotVariants(?string $project_id): ?ImageVariants
  {
    if (null === $project_id || '' === $project_id) {
      return null;
    }

    return $this->project_manager->getScreenshotVariants($project_id);
  }
}
