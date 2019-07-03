<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\RecommenderSystem\RecommendedPageId;
use App\Catrobat\Services\StatisticsService;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;


/**
 * Class DownloadStatisticsListener
 * @package App\Catrobat\Listeners
 */
class DownloadStatisticsListener
{
  /**
   * @var StatisticsService
   */
  private $statistics_service;
  /**
   * @var
   */
  private $security_token_storage;

  /**
   * DownloadStatisticsListener constructor.
   *
   * @param $statistics_service
   * @param $security_token_storage
   */
  public function __construct($statistics_service, $security_token_storage)
  {
    $this->statistics_service = $statistics_service;
    $this->security_token_storage = $security_token_storage;
  }

  /**
   * @param PostResponseEvent $event
   */
  public function onTerminateEvent(PostResponseEvent $event)
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
          $rec_user_specific = (bool)$attributes->get('rec_user_specific');
        }
      }
      else
      {
        if ($attributes->has('rec_from'))
        {
          // tag-recommendations
          $rec_tag_by_program_id = $attributes->get('rec_from');
        }
      }

      $this->createProgramDownloadStatistics($request, $program_id, $referrer, $rec_tag_by_program_id,
        $rec_by_page_id, $rec_by_program_id, $locale, $rec_user_specific);
      $event->getRequest()->attributes->remove('download_statistics_program_id');
    }
  }

  /**
   * @param $request
   * @param $program_id
   * @param $referrer
   * @param $rec_tag_by_program_id
   * @param $rec_by_page_id
   * @param $rec_by_program_id
   * @param $locale
   * @param $is_user_specific_recommendation
   */
  public function createProgramDownloadStatistics($request, $program_id, $referrer, $rec_tag_by_program_id,
                                                  $rec_by_page_id, $rec_by_program_id, $locale,
                                                  $is_user_specific_recommendation)
  {
    if ((strpos($request->headers->get('User-Agent'), 'okhttp') === false) || ($rec_by_page_id != null))
    {
      $this->statistics_service->createProgramDownloadStatistics($request, $program_id, $referrer,
        $rec_tag_by_program_id, $rec_by_page_id, $rec_by_program_id, $locale, $is_user_specific_recommendation);
    }
  }
}
