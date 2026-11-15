<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System;

use App\System\EventListener\PreloadLinkHeaderListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 */
#[CoversClass(PreloadLinkHeaderListener::class)]
final class PreloadLinkHeaderTest extends WebTestCase
{
  #[Group('functional')]
  public function testPagePreloadsTheAssetsItRenders(): void
  {
    $client = self::createClient();
    $client->request('GET', '/app/login');
    $response = $client->getResponse();
    $this->assertSame(200, $response->getStatusCode());

    $header = (string) $response->headers->get('Link');
    preg_match_all('#<([^>]+)>; rel=preload; as=(style|script); crossorigin#', $header, $links, PREG_SET_ORDER);
    $this->assertNotEmpty($links, 'Link header: '.$header);
    $this->assertCount(count(explode(', ', $header)), $links, 'every link is a crossorigin preload: '.$header);

    $html = (string) $response->getContent();
    $public_dir = dirname(__DIR__, 3).'/public';
    $as_seen = [];
    foreach ($links as [, $url, $as]) {
      // Same URL as in the HTML, or the browser fetches it twice
      $this->assertStringContainsString(('style' === $as ? 'href="' : 'src="').$url.'"', $html);
      $this->assertFileExists($public_dir.$url);
      $as_seen[] = $as;
    }
    $this->assertContains('style', $as_seen);
    $this->assertContains('script', $as_seen);
    $this->assertStringNotContainsString('/chunks/', $header, 'modulepreload chunks stay out of the header');
  }

  #[Group('functional')]
  public function testApiResponsesHaveNoLinkHeader(): void
  {
    $client = self::createClient();
    $client->request('GET', '/api/health');

    $this->assertFalse($client->getResponse()->headers->has('Link'));
  }

  #[Group('functional')]
  public function testErrorPagesHaveNoLinkHeader(): void
  {
    $client = self::createClient();
    $client->request('GET', '/this-page-does-not-exist');

    $this->assertSame(404, $client->getResponse()->getStatusCode());
    $this->assertStringContainsString('/build/', (string) $client->getResponse()->getContent(), 'the error page renders Vite assets');
    $this->assertFalse($client->getResponse()->headers->has('Link'));
  }
}
