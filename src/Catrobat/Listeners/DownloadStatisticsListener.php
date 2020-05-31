<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\RecommenderSystem\RecommendedPageId;
use App\Catrobat\Services\StatisticsService;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;

class DownloadStatisticsListener
{
  private StatisticsService $statistics_service;

  private TokenStorageInterface $security_token_storage;

  public function __construct(StatisticsService $statistics_service, TokenStorageInterface $security_token_storage)
  {
    $this->statistics_service = $statistics_service;
    $this->security_token_storage = $security_token_storage;
  }

  /**
   * @throws Exception
   */
  public function onTerminateEvent(TerminateEvent $event): void
  {
    $request = $event->getRequest();
    $attributes = $request->attributes;

    if ($attributes->has('download_statistics_program_id'))
    {
      $program_id = $attributes->get('download_statistics_program_id');
      $referrer = $attributes->get('referrer');
      $locale = strtolower($request->getLocale());

      $rec_by_page_id = null;
      $rec_by_program_id = 0;
      $rec_user_specific = false;

      $rec_tag_by_program_id = null;

      if ($attributes->has('rec_by_page_id') && RecommendedPageId::isValidRecommendedPageId($attributes->get('rec_by_page_id')))
      {
        // all recommendations (except tag-recommendations -> see below)
        $rec_by_page_id = $attributes->get('rec_by_page_id');
        if ($attributes->has('rec_by_program_id'))
        {
          $rec_by_program_id = $attributes->get('rec_by_program_id');
        }
        if ($attributes->has('rec_user_specific'))
        {
          $rec_user_specific = (bool) $attributes->get('rec_user_specific');
        }
      }
      elseif ($attributes->has('rec_from'))
      {
        // tag-recommendations
        $rec_tag_by_program_id = $attributes->get('rec_from');
      }

      $this->createProgramDownloadStatistics($request, $program_id, $referrer, $rec_tag_by_program_id,
        $rec_by_page_id, $rec_by_program_id, $locale, $rec_user_specific);
      $event->getRequest()->attributes->remove('download_statistics_program_id');
    }
  }

  /**
   * @throws Exception
   */
  public function createProgramDownloadStatistics(Request $request, string $program_id, ?string $referrer,
                                                  ?string $rec_tag_by_program_id, ?int $rec_by_page_id,
                                                  ?string $rec_by_program_id, ?string $locale, bool $is_user_specific_recommendation = false): bool
  {
    if ((false === strpos($request->headers->get('User-Agent'), 'okhttp')) || (null != $rec_by_page_id))
    {
      return $this->statistics_service->createProgramDownloadStatistics($request, $program_id, $referrer,
        $rec_tag_by_program_id, $rec_by_page_id, $rec_by_program_id, $locale, $is_user_specific_recommendation);
    }

    return false;
  }
}
