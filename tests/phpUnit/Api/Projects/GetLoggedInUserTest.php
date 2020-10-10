<?php

namespace Tests\phpUnit\Api\Projects;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 * @covers \App\Api\ProjectsApi
 */
class GetLoggedInUserTest extends WebTestCase
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

    $client->request('POST', '/api/authentication', [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'], '{"username":"Catrobat", "password":"123456"}');
    $this->assertResponseStatusCodeSame(200);
    $response = $client->getResponse();
    $data = json_decode($response->getContent(), true);
    /** @var string $token */
    $token = $data['token'] ?? null;

    $client->request('GET', '/api/projects/user/', ['limit' => 2], [], ['HTTP_ACCEPT' => 'application/json', 'HTTP_authorization' => 'Bearer '.$token]);
    $this->assertResponseStatusCodeSame(200);

    $client->request('GET', '/api/projects/user/', ['limit' => 2, 'offset' => 2], [], ['HTTP_ACCEPT' => 'application/json', 'HTTP_authorization' => 'Bearer '.$token]);
    $this->assertResponseStatusCodeSame(200);

    $client->request('GET', '/api/projects/user/', ['offset' => 2], [], ['HTTP_ACCEPT' => 'application/json', 'HTTP_authorization' => 'Bearer '.$token]);
    $this->assertResponseStatusCodeSame(200);

    $client->request('GET', '/api/projects/user/', ['max_version' => '0.984'], [], ['HTTP_ACCEPT' => 'application/json', 'HTTP_authorization' => 'Bearer '.$token]);
    $this->assertResponseStatusCodeSame(200);

    $client->request('GET', '/api/projects/user/', ['flavor' => 'luna'], [], ['HTTP_ACCEPT' => 'application/json', 'HTTP_authorization' => 'Bearer '.$token]);
    $this->assertResponseStatusCodeSame(200);

    $client->request('GET', '/api/projects/user/', [], [], ['HTTP_ACCEPT' => 'application/json', 'HTTP_authorization' => 'Bearer '.$token]);
    $this->assertResponseStatusCodeSame(200);
  }
}
