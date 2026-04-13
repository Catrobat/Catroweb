<?php

declare(strict_types=1);

namespace App\Twig;

use App\Project\ProjectManager;
use App\Studio\StudioManager;
use App\User\UserAvatarService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Exposes the shared AVIF/WebP variant builders to Twig so server-rendered
 * pages can emit the same responsive `<picture>` markup the API returns.
 */
class ImageVariantsExtension extends AbstractExtension
{
  public function __construct(
    private readonly StudioManager $studio_manager,
    private readonly UserAvatarService $user_avatar_service,
    private readonly ProjectManager $project_manager,
  ) {
  }

  #[\Override]
  public function getFunctions(): array
  {
    return [
      new TwigFunction('studio_cover_variants', $this->studio_manager->getCoverVariants(...)),
      new TwigFunction('user_avatar_variants', $this->user_avatar_service->getVariants(...)),
      new TwigFunction('project_screenshot_variants', $this->project_manager->getScreenshotVariants(...)),
    ];
  }
}
