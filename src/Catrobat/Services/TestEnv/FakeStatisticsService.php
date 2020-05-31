<?php

namespace App\Catrobat\Services\TestEnv;

use App\Catrobat\Services\StatisticsService;
use App\Entity\ProgramManager;
use App\Repository\ExtensionRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FakeStatisticsService extends StatisticsService
{
  private StatisticsService $geocoder_service;

  private bool $use_real_service = false;

  public function __construct(StatisticsService $geocoder_service, ProgramManager $program_manager,
                              EntityManagerInterface $entity_manager, LoggerInterface $logger,
                              TokenStorageInterface $security_token_storage, ExtensionRepository $extension_repository,
                              TagRepository $tag_repository)
  {
    parent::__construct(
      $program_manager, $entity_manager, $logger, $security_token_storage, $extension_repository, $tag_repository
    );
    $this->geocoder_service = $geocoder_service;
  }

  /**
   * @throws Exception
   */
  public function createProgramDownloadStatistics(Request $request, string $program_id, ?string $referrer,
                                                  ?string $rec_tag_by_program_id, ?int $rec_by_page_id,
                                                  ?string $rec_by_program_id, ?string $locale, bool $is_user_specific_recommendation = false): bool
  {
    if ($this->use_real_service)
    {
      return $this->geocoder_service->createProgramDownloadStatistics($request, $program_id, $referrer,
        $rec_tag_by_program_id, $rec_by_page_id, $rec_by_program_id, $locale);
    }

    return true;
  }

  /**
   * @throws Exception
   */
  public function createClickStatistics(Request $request, string $type, ?string $rec_from_id, ?string $rec_program_id,
                                        ?int $tag_id, ?string $extension_name,
                                        ?string $referrer, ?string $locale = null,
                                        bool $is_recommended_program_a_scratch_program = false,
                                        bool $is_user_specific_recommendation = false): bool
  {
    return $this->geocoder_service->createClickStatistics($request, $type, $rec_from_id, $rec_program_id,
      $tag_id, $extension_name, $referrer);
  }

  /**
   * @throws Exception
   */
  public function createHomepageProgramClickStatistics(Request $request, string $type, string $program_id, ?string $referrer, ?string $locale): bool
  {
    return $this->geocoder_service->createHomepageProgramClickStatistics($request, $type, $program_id, $referrer, $locale);
  }

  public function useRealService(bool $use_real): void
  {
    $this->use_real_service = $use_real;
  }
}
