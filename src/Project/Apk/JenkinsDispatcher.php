<?php

declare(strict_types=1);

namespace App\Project\Apk;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\RouterInterface;

class JenkinsDispatcher
{
  protected array $config;

  /**
   * JenkinsDispatcher constructor.
   *
   * @throws \Exception
   */
  public function __construct(
    #[Autowire('%jenkins%')]
    array $config,
    protected RouterInterface $router,
  ) {
    if (!isset($config['url'])) {
      throw new \Exception();
    }

    $this->config = $config;
  }

  public function sendBuildRequest(string $id): string
  {
    $params = [
      'job' => $this->config['job'],
      'token' => $this->config['token'],
      'SUFFIX' => 'generated'.$id,
      'DOWNLOAD' => $this->router->generate('open_api_server_projects_projectidcatrobatget', ['id' => $id], $this->router::ABSOLUTE_URL),
      'UPLOAD' => $this->router->generate('ci_upload_apk', ['id' => $id, 'token' => $this->config['uploadtoken']], $this->router::ABSOLUTE_URL),
      'ONERROR' => $this->router->generate('ci_failed_apk', ['id' => $id, 'token' => $this->config['uploadtoken']], $this->router::ABSOLUTE_URL),
    ];

    return $this->dispatch($params);
  }

  protected function dispatch(mixed $params): string
  {
    $url = $this->config['url'].'?'.http_build_query($params);
    file_get_contents($url);

    return $url;
  }
}
