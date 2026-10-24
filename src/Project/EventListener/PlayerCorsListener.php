<?php

declare(strict_types=1);

namespace App\Project\EventListener;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * The browser player runs on its own host (PLAYER_ORIGIN), so share's cookies never reach project code; it reads
 * project files from here. Without credentials: only what a logged-out visitor may see.
 * nginx adds the same header to the files it serves via X-Accel-Redirect, which drops this one.
 */
#[AsEventListener(method: 'allowPlayer')]
class PlayerCorsListener
{
  public function __construct(
    #[Autowire('%env(PLAYER_ORIGIN)%')]
    private readonly string $player_origin,
  ) {
  }

  public function allowPlayer(ResponseEvent $event): void
  {
    if ('' === $this->player_origin || !str_starts_with($event->getRequest()->getPathInfo(), '/project-files/')) {
      return;
    }
    $headers = $event->getResponse()->headers;
    $headers->set('Access-Control-Allow-Origin', $this->player_origin);
    $headers->set('Vary', 'Origin', false);
  }
}
