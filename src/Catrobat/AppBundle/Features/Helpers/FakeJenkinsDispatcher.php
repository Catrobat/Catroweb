<?php

namespace Catrobat\AppBundle\Features\Helpers;

use Catrobat\AppBundle\Services\Ci\JenkinsDispatcher;

class FakeJenkinsDispatcher extends JenkinsDispatcher
{
  protected $last_params;

  protected function dispatch($params)
  {
    $this->last_params = $params;

    return $this->config['url'] . '?' . http_build_query($params);
  }

  public function getLastParameters()
  {
    return $this->last_params;
  }
}
