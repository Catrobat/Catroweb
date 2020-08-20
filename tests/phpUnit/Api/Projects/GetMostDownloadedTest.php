<?php

namespace Tests\phpUnit\Api\Projects;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 * @covers \App\Api\ProjectsApi
 */
class GetMostDownloadedTest extends WebTestCase
{
  /**
   * {@inheritdoc}
   */
  public function setUp(): void
  {
    static::$kernel = static::createKernel();
    static::$kernel->boot();
  }

  /**
   * {@inheritdoc}
   */
  protected function tearDown(): void
  {
    parent::tearDown();
  }

  public function testProjects(): void
  {
    $client = static::createClient();

    $client->request('GET', '/api/projects', [], [], []);
    $this->assertResponseStatusCodeSame(406);

    $client->request('GET', '/api/projects', ['category' => 'most_downloaded'], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);

    $client->request('GET', '/api/projects', ['category' => 'most_downloaded', 'limit' => 1], [], ['HTTP_ACCEPT' => 'application/json', 'HTTP_ACCEPT_LANGUAGE' => 'de']);
    $this->assertResponseStatusCodeSame(200);

    $client->request('GET', '/api/projects', ['category' => 'most_downloaded', 'offset' => 1], [], ['HTTP_ACCEPT' => 'application/json', 'HTTP_ACCEPT_LANGUAGE' => 'en']);
    $this->assertResponseStatusCodeSame(200);

    $client->request('GET', '/api/projects', ['category' => 'most_downloaded', 'max_version' => '0.982'], [], ['HTTP_ACCEPT' => 'application/json', 'HTTP_ACCEPT_LANGUAGE' => 'fr']);
    $this->assertResponseStatusCodeSame(200);

    $client->request('GET', '/api/projects', ['category' => 'most_downloaded', 'flavor' => 'luna'], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);
  }
}
