<?php

namespace App\Catrobat\Services\TestEnv;

use App\Catrobat\Services\Ci\JenkinsDispatcher;

/**
 * Class FakeJenkinsDispatcher.
 */
class FakeJenkinsDispatcher extends JenkinsDispatcher
{
  /**
   * @var
   */
  protected $last_params;

  /**
   * @return mixed
   */
  public function getLastParameters()
  {
    return $this->last_params;
  }

  /**
   * @param $params
   *
   * @return string
   */
  protected function dispatch($params)
  {
    $this->last_params = $params;

    return $this->config['url'].'?'.http_build_query($params);
  }
}
