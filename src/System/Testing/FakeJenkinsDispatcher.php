<?php

namespace App\System\Testing;

use App\Project\Apk\JenkinsDispatcher;

class FakeJenkinsDispatcher extends JenkinsDispatcher
{
  /**
   * @var mixed
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
   * @param mixed $params
   */
  protected function dispatch($params): string
  {
    $this->last_params = $params;

    return $this->config['url'].'?'.http_build_query($params);
  }
}
