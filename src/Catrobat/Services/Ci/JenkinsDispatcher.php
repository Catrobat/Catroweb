<?php

namespace App\Catrobat\Services\Ci;



use Symfony\Component\Routing\RouterInterface;

/**
 * Class JenkinsDispatcher
 * @package App\Catrobat\Services\Ci
 */
class JenkinsDispatcher
{
  /**
   * @var RouterInterface
   */
  protected $router;
  /**
   * @var
   */
  protected $config;

  /**
   * JenkinsDispatcher constructor.
   *
   * @param $router
   * @param $config
   *
   * @throws \Exception
   */
  public function __construct($config, RouterInterface $router)
  {
    if (!isset($config['url']))
    {
      throw new \Exception();
    }
    $this->config = $config;
    $this->router = $router;
  }

  /**
   * @param $id
   *
   * @return string
   */
  public function sendBuildRequest($id)
  {
    $params = [
      'job'      => $this->config['job'],
      'token'    => $this->config['token'],
      'SUFFIX'   => 'generated' . $id,
      'DOWNLOAD' => $this->router->generate('download', ['id' => $id], $this->router::ABSOLUTE_URL),
      'UPLOAD'   => $this->router->generate('ci_upload_apk', ['id' => $id, 'token' => $this->config['uploadtoken']], $this->router::ABSOLUTE_URL),
      'ONERROR'  => $this->router->generate('ci_failed_apk', ['id' => $id, 'token' => $this->config['uploadtoken']], $this->router::ABSOLUTE_URL),
    ];

    return $this->dispatch($params);
  }

  /**
   * @param $params
   *
   * @return string
   */
  protected function dispatch($params)
  {
    $url = $this->config['url'] . '?' . http_build_query($params);
    file_get_contents($url);

    return $url;
  }
}
