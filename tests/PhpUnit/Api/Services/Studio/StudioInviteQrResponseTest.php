<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\Studio;

use App\Api\Services\Studio\StudioResponseManager;
use App\DB\Entity\Studio\Studio;
use App\DB\Entity\Studio\StudioInvite;
use App\Project\ProjectManager;
use App\Studio\StudioManager;
use App\User\UserAvatarService;
use OpenAPI\Server\Service\SerializerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @internal
 */
#[CoversClass(StudioResponseManager::class)]
#[Group('unit')]
final class StudioInviteQrResponseTest extends TestCase
{
  private function responseManager(): StudioResponseManager
  {
    $urls = $this->createStub(UrlGeneratorInterface::class);
    $urls->method('generate')->willReturnCallback(static fn (string $name, array $parameters): string => 'https://share.catrobat.org/app/studio/join/'.$parameters['code']);
    $parameters = $this->createStub(ParameterBagInterface::class);
    $parameters->method('get')->willReturn('pocketcode');

    return new StudioResponseManager(
      $this->createStub(TranslatorInterface::class),
      $this->createStub(SerializerInterface::class),
      $this->createStub(CacheItemPoolInterface::class),
      $urls,
      $parameters,
      $this->createStub(StudioManager::class),
      $this->createStub(ProjectManager::class),
      $this->createStub(UserAvatarService::class),
    );
  }

  public function testUsableInviteContainsAnInlineSvgQrCode(): void
  {
    $response = $this->responseManager()->createInviteResponse(new StudioInvite(new Studio(), 'CLASS1CODE', null, null, null));
    self::assertSame('https://share.catrobat.org/app/studio/join/CLASS1CODE', $response->getUrl());
    $data = $response->getQrCode();
    self::assertNotNull($data);
    self::assertStringStartsWith('data:image/svg+xml;base64,', $data);
    $svg = base64_decode(substr($data, strlen('data:image/svg+xml;base64,')), true);
    self::assertNotFalse($svg);
    $image = simplexml_load_string($svg);
    self::assertNotFalse($image);
    self::assertSame('svg', $image->getName());
    self::assertSame('288px', (string) $image['width']);
    self::assertStringNotContainsString('<script', $svg);
    self::assertStringNotContainsString('<image', $svg);
  }

  public function testDifferentInviteLinksGenerateDifferentQrImages(): void
  {
    $manager = $this->responseManager();
    $first = $manager->createInviteResponse(new StudioInvite(new Studio(), 'CLASS1CODE', null, null, null));
    $second = $manager->createInviteResponse(new StudioInvite(new Studio(), 'CLASS2CODE', null, null, null));
    self::assertNotSame($first->getQrCode(), $second->getQrCode());
  }

  public function testExpiredInviteContainsNoQrImage(): void
  {
    $invite = new StudioInvite(new Studio(), 'CLASS1CODE', null, null, new \DateTime('-1 second'));
    self::assertNull($this->responseManager()->createInviteResponse($invite)->getQrCode());
  }

  public function testExhaustedInviteContainsNoQrImage(): void
  {
    $invite = new StudioInvite(new Studio(), 'CLASS1CODE', null, 1, null);
    new \ReflectionProperty(StudioInvite::class, 'uses')->setValue($invite, 1);
    self::assertNull($this->responseManager()->createInviteResponse($invite)->getQrCode());
  }
}
