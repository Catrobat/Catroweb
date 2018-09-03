<?php

namespace Catrobat\AppBundle\Features\Helpers;

use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Services\FacebookPostService;
use Catrobat\AppBundle\StatusCode;

class FakeFacebookPostService
{

  private $facebook_service;
  private $use_real_service;

  public function __construct(FacebookPostService $facebook_service)
  {
    $this->facebook_service = $facebook_service;
  }

  public function removeFbPost($post_id)
  {
    if ($this->use_real_service)
    {
      return $this->facebook_service->removeFbPost($post_id);
    }

    return StatusCode::OK;
  }

  public function postOnFacebook($program_id)
  {
    if ($this->use_real_service)
    {
      return $this->facebook_service->postOnFacebook($program_id);
    }
    $fake_facebook_post_id = -1;

    return $fake_facebook_post_id;
  }

  public function checkFacebookPostAvailable($post_id)
  {
    if ($this->use_real_service)
    {
      return $this->facebook_service->checkFacebookPostAvailable($post_id);
    }
    throw new \Exception('Function not implemented in FakeFacebookPostService');
  }

  public function useRealService($use_real)
  {
    $this->use_real_service = $use_real;
  }
}
