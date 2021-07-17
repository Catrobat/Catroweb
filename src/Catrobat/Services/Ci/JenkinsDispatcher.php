<?php

namespace App\Catrobat\Services\Ci;

use Exception;
use Symfony\Component\Routing\RouterInterface;

class JenkinsDispatcher
{
  protected RouterInterface $router;

  protected array $config;

  /**
   * JenkinsDispatcher constructor.
   *
   * @throws Exception
   */
  public function __construct(array $config, RouterInterface $router)
  {
    if (!isset($config['url'])) {
      throw new Exception();
    }
    $this->config = $config;
    $this->router = $router;
  }

  public function sendBuildRequest(string $id): string
  {
    $params = [
      'job' => $this->config['job'],
      'token' => $this->config['token'],
      'SUFFIX' => 'generated'.$id,
      'DOWNLOAD' => $this->router->generate('download', ['id' => $id], $this->router::ABSOLUTE_URL),
      'UPLOAD' => $this->router->generate('ci_upload_apk', ['id' => $id, 'token' => $this->config['uploadtoken']], $this->router::ABSOLUTE_URL),
      'ONERROR' => $this->router->generate('ci_failed_apk', ['id' => $id, 'token' => $this->config['uploadtoken']], $this->router::ABSOLUTE_URL),
    ];

    return $this->dispatch($params);
  }

  /**
   * @param mixed $params
   */
  protected function dispatch($params): string
  {
    var_dump($params);
    dd($this->config['url']);
    $url = $this->config['url'].'?'.http_build_query($params);
    file_get_contents($url);

    return $url;
  }

  public static function sendSigningRequest(string $url, array $params)
  {
    $url = $url .'?' . http_build_query($params);
    file_get_contents($url);

    return $url;
  }
}
