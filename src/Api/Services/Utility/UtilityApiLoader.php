<?php

declare(strict_types=1);

namespace App\Api\Services\Utility;

use App\Api\Services\Base\AbstractApiLoader;
use App\DB\Entity\FeaturedBanner;
use App\DB\Entity\Flavor;
use App\DB\Entity\System\Survey;
use App\DB\EntityRepository\FeaturedBannerRepository;
use Doctrine\ORM\EntityManagerInterface;

class UtilityApiLoader extends AbstractApiLoader
{
  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
    private readonly FeaturedBannerRepository $featured_banner_repository,
  ) {
  }

  /**
   * @return FeaturedBanner[]
   */
  public function getActiveBanners(int $limit, int $offset): array
  {
    return $this->featured_banner_repository->findActiveBanners($limit, $offset);
  }

  /**
   * @return FeaturedBanner[]
   */
  public function getActiveBannersKeyset(int $limit, ?int $cursor_priority = null, ?string $cursor_id = null): array
  {
    return $this->featured_banner_repository->findActiveBannersKeyset($limit, $cursor_priority, $cursor_id);
  }

  public function getSurvey(array $criteria): ?Survey
  {
    $survey_repo = $this->entity_manager->getRepository(Survey::class);

    return $survey_repo->findOneBy($criteria);
  }

  public function getSurveyFlavor(string $flavor): ?Flavor
  {
    $flavor_repo = $this->entity_manager->getRepository(Flavor::class);
    $criteria = ['name' => $flavor];

    return $flavor_repo->findOneBy($criteria);
  }
}
