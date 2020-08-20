<?php

namespace Tests\phpUnit\Api\Projects;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 * @covers \App\Api\ProjectsApi
 */
class GetSearchTest extends WebTestCase
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

    $client->request('GET', '/api/projects/search', [], [], []);
    $this->assertResponseStatusCodeSame(406);

    $client->request('GET', '/api/projects/search', ['query' => 'Galaxy'], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);

    $client->request('GET', '/api/projects/search', ['query' => 'User 1'], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);

    $client->request('GET', '/api/projects/search', ['query' => 'Project', 'limit' => 2], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);

    $client->request('GET', '/api/projects/search', ['query' => 'NewUser', 'limit' => 2], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);

    $client->request('GET', '/api/projects/search', ['query' => 'NewUser', 'offset' => 1], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);

    $client->request('GET', '/api/projects/search', ['query' => 'Arduino'], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);

    $client->request('GET', '/api/projects/search', ['query' => ''], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);
  }
}
