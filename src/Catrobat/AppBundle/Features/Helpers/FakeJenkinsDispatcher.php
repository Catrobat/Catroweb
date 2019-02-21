<?php

namespace Catrobat\AppBundle\Features\Helpers;

use Catrobat\AppBundle\Services\Ci\JenkinsDispatcher;

/**
 * Class FakeJenkinsDispatcher
 * @package Catrobat\AppBundle\Features\Helpers
 */
class FakeJenkinsDispatcher extends JenkinsDispatcher
{
  /**
   * @var
   */
  protected $last_params;

  /**
   * @param $params
   *
   * @return string
   */
  protected function dispatch($params)
  {
    $this->last_params = $params;

    return $this->config['url'] . '?' . http_build_query($params);
  }

  /**
   * @return mixed
   */
  public function getLastParameters()
  {
    return $this->last_params;
  }
}
