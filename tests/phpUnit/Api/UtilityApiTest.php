<?php

namespace Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers \App\Api\UtilityApi
 *
 * @internal
 */
class UtilityApiTest extends WebTestCase
{
  public function testGetHealth(): void
  {
    $client = static::createClient();

    $client->request('GET', '/api/health', [], [], []);
    $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
  }
}
