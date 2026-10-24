<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\EventListener;

use App\Project\EventListener\PlayerCorsListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @internal
 */
#[CoversClass(PlayerCorsListener::class)]
final class PlayerCorsListenerTest extends TestCase
{
  public function testProjectFilesAllowThePlayer(): void
  {
    $response = $this->respond('/project-files/abc-123/assets/code.xml', 'https://play.catrobat.org');

    $this->assertSame('https://play.catrobat.org', $response->headers->get('Access-Control-Allow-Origin'));
    $this->assertNull($response->headers->get('Access-Control-Allow-Credentials'));
  }

  public function testDeniedProjectFilesAllowThePlayerToo(): void
  {
    $response = $this->respond('/project-files/abc-123/assets/Scene/images/missing.png', 'https://play.catrobat.org', 404);

    $this->assertSame('https://play.catrobat.org', $response->headers->get('Access-Control-Allow-Origin'));
  }

  public function testOtherPathsStayClosed(): void
  {
    $response = $this->respond('/api/projects', 'https://play.catrobat.org');

    $this->assertNull($response->headers->get('Access-Control-Allow-Origin'));
  }

  public function testNoPlayerOriginConfigured(): void
  {
    $response = $this->respond('/project-files/abc-123/assets/code.xml', '');

    $this->assertNull($response->headers->get('Access-Control-Allow-Origin'));
  }

  private function respond(string $path, string $player_origin, int $status = 200): Response
  {
    $response = new Response('', $status);
    $event = new ResponseEvent($this->createStub(HttpKernelInterface::class), Request::create($path), HttpKernelInterface::MAIN_REQUEST, $response);
    (new PlayerCorsListener($player_origin))->allowPlayer($event);

    return $response;
  }
}
