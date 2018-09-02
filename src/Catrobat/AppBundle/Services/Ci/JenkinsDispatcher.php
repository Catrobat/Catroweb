<?php

namespace Catrobat\AppBundle\Services\Ci;

class JenkinsDispatcher
{
  protected $router;
  protected $config;

  public function __construct($router, $config)
  {
    if (!isset($config['url']))
    {
      throw new \Exception();
    }
    $this->config = $config;
    $this->router = $router;
  }

  public function sendBuildRequest($id)
  {
    $params = [
      'job'      => $this->config['job'],
      'token'    => $this->config['token'],
      'SUFFIX'   => 'generated' . $id,
      'DOWNLOAD' => $this->router->generate('download', ['id' => $id], true),
      'UPLOAD'   => $this->router->generate('ci_upload_apk', ['id' => $id, 'token' => $this->config['uploadtoken']], true),
      'ONERROR'  => $this->router->generate('ci_failed_apk', ['id' => $id, 'token' => $this->config['uploadtoken']], true),
    ];

    return $this->dispatch($params);
  }

  protected function dispatch($params)
  {
    $url = $this->config['url'] . '?' . http_build_query($params);
    file_get_contents($url);

    return $url;
  }
}
