<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Application\Layout;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

/**
 * @internal
 *
 * @coversNothing
 */
class CloudflareWebAnalyticsTest extends KernelTestCase
{
  public function testRendersTheBeaconWithTheToken(): void
  {
    $html = $this->render('54c76c1befef4d60ba9bef1b83b11909');

    $this->assertStringContainsString('src="https://static.cloudflareinsights.com/beacon.min.js"', $html);
    $this->assertSame(1, preg_match('/data-cf-beacon="([^"]*)"/', $html, $m));
    $this->assertSame('{"token":"54c76c1befef4d60ba9bef1b83b11909"}', html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5));
  }

  public function testRendersNothingWithoutAToken(): void
  {
    $this->assertSame('', trim($this->render('')));
  }

  public function testOtherEnvironmentsSendNothing(): void
  {
    $twig = self::getContainer()->get('twig');
    $this->assertInstanceOf(Environment::class, $twig);
    $this->assertSame('', $twig->getGlobals()['cloudflare_web_analytics_token']);
  }

  private function render(string $token): string
  {
    $twig = self::getContainer()->get('twig');
    $this->assertInstanceOf(Environment::class, $twig);

    return $twig->render('Layout/CloudflareWebAnalytics.html.twig', ['token' => $token]);
  }
}
