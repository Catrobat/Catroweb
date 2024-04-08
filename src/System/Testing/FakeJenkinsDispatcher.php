<?php

declare(strict_types=1);

namespace App\System\Testing;

use App\Project\Apk\JenkinsDispatcher;

class FakeJenkinsDispatcher extends JenkinsDispatcher
{
  protected mixed $last_params = null;

  public function getLastParameters(): mixed
  {
    return $this->last_params;
  }

  protected function dispatch(mixed $params): string
  {
    $this->last_params = $params;

    return $this->config['url'].'?'.http_build_query($params);
  }
}
