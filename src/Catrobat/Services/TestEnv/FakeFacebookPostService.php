<?php

namespace App\Catrobat\Services\TestEnv;

use App\Catrobat\Services\FacebookPostService;
use App\Catrobat\StatusCode;

/**
 * Class FakeFacebookPostService
 * @package App\Catrobat\Features\Helpers
 */
class FakeFacebookPostService
{

  /**
   * @var FacebookPostService
   */
  private $facebook_service;
  /**
   * @var
   */
  private $use_real_service;

  /**
   * FakeFacebookPostService constructor.
   *
   * @param FacebookPostService $facebook_service
   */
  public function __construct(FacebookPostService $facebook_service)
  {
    $this->facebook_service = $facebook_service;
  }

  /**
   * @param $post_id
   *
   * @return int
   * @throws \Facebook\Exceptions\FacebookSDKException
   */
  public function removeFbPost($post_id)
  {
    if ($this->use_real_service)
    {
      return $this->facebook_service->removeFbPost($post_id);
    }

    return StatusCode::OK;
  }

  /**
   * @param $program_id
   *
   * @return int
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   * @throws \Facebook\Exceptions\FacebookSDKException
   */
  public function postOnFacebook($program_id)
  {
    if ($this->use_real_service)
    {
      return $this->facebook_service->postOnFacebook($program_id);
    }
    $fake_facebook_post_id = -1;

    return $fake_facebook_post_id;
  }

  /**
   * @param $post_id
   *
   * @return int
   * @throws \Facebook\Exceptions\FacebookSDKException
   */
  public function checkFacebookPostAvailable($post_id)
  {
    if ($this->use_real_service)
    {
      return $this->facebook_service->checkFacebookPostAvailable($post_id);
    }
    throw new \Exception('Function not implemented in FakeFacebookPostService');
  }

  /**
   * @param $use_real
   */
  public function useRealService($use_real)
  {
    $this->use_real_service = $use_real;
  }
}
