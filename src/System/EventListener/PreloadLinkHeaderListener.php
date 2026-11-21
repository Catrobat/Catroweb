<?php

declare(strict_types=1);

namespace App\System\EventListener;

use Pentatrion\ViteBundle\Service\EntrypointRenderer;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Sends `Link: <…>; rel=preload` for the stylesheets a page rendered, so Cloudflare can answer the next
 * visitor with a 103 Early Hints while the origin still builds the page.
 *
 * Only stylesheets: they block the first paint. Hinting the deferred scripts too made them share the
 * line with the CSS and pushed the first paint back on slow phones.
 *
 * The URLs come from the Vite bundle's own list of rendered tags, so they match the HTML byte for byte
 * (otherwise the browser downloads twice).
 */
#[AsEventListener(event: KernelEvents::RESPONSE, priority: -10)]
class PreloadLinkHeaderListener
{
  public function __construct(private readonly EntrypointRenderer $entrypoint_renderer)
  {
  }

  public function __invoke(ResponseEvent $event): void
  {
    $response = $event->getResponse();
    if (!$event->isMainRequest()
      || Response::HTTP_OK !== $response->getStatusCode()
      || !str_starts_with((string) $response->headers->get('Content-Type'), 'text/html')
      || $response->headers->has('Link')) {
      return;
    }

    $links = [];
    foreach ($this->entrypoint_renderer->getRenderedTags() as $tag) {
      if ($tag->isStylesheet()) {
        $links[] = $this->link($tag->getAttribute('href'), $tag->getAttribute('crossorigin'));
      }
    }

    $links = array_values(array_unique(array_filter($links)));
    if ([] !== $links) {
      $response->headers->set('Link', implode(', ', $links));
    }
  }

  private function link(mixed $url, mixed $crossorigin): ?string
  {
    if (!is_string($url) || '' === $url) {
      return null;
    }

    $link = sprintf('<%s>; rel=preload; as=style', $url);
    if (true === $crossorigin || '' === $crossorigin || 'anonymous' === $crossorigin) {
      $link .= '; crossorigin';
    } elseif (is_string($crossorigin)) {
      $link .= '; crossorigin='.$crossorigin;
    }

    return $link;
  }
}
