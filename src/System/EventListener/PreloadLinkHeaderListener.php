<?php

declare(strict_types=1);

namespace App\System\EventListener;

use Pentatrion\ViteBundle\Service\EntrypointRenderer;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Sends `Link: <…>; rel=preload` for the stylesheets and entry scripts a page rendered, so Cloudflare
 * can answer the next visitor with a 103 Early Hints while the origin still builds the page.
 *
 * The URLs come from the Vite bundle's own list of rendered tags, so they match the HTML byte for byte
 * (otherwise the browser downloads twice). modulepreload chunks are left out: Cloudflare only forwards
 * rel=preload/preconnect, and they would only bloat the header.
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

    $styles = [];
    $scripts = [];
    foreach ($this->entrypoint_renderer->getRenderedTags() as $tag) {
      if ($tag->isStylesheet()) {
        $styles[] = $this->link($tag->getAttribute('href'), 'style', $tag->getAttribute('crossorigin'));
      } elseif ($tag->isScriptTag()) {
        $scripts[] = $this->link($tag->getAttribute('src'), 'script', $tag->getAttribute('crossorigin'));
      }
    }

    // Stylesheets first: they block rendering, the deferred scripts do not.
    $links = array_values(array_unique(array_filter([...$styles, ...$scripts])));
    if ([] !== $links) {
      $response->headers->set('Link', implode(', ', $links));
    }
  }

  private function link(mixed $url, string $as, mixed $crossorigin): ?string
  {
    if (!is_string($url) || '' === $url) {
      return null;
    }

    $link = sprintf('<%s>; rel=preload; as=%s', $url, $as);
    if (true === $crossorigin || '' === $crossorigin || 'anonymous' === $crossorigin) {
      $link .= '; crossorigin';
    } elseif (is_string($crossorigin)) {
      $link .= '; crossorigin='.$crossorigin;
    }

    return $link;
  }
}
