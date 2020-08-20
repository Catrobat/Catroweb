<?php

namespace Tests\phpUnit\Api\Projects;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 * @covers \App\Api\ProjectsApi
 */
class GetFeaturedTest extends WebTestCase
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

    $client->request('GET', '/api/projects/featured', ['limit' => 'nolimit'], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(400);

    $client->request('GET', '/api/projects/featured', [], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);
    $data = $client->getResponse()->getContent();
    $this->assertJsonStringEqualsJsonString($data, '[{"id":"1","name":"Project 3","author":"Catrobat","featured_image":"http:\/\/localhost\/resources_test\/featured\/featured_1.test"},{"id":"2","name":"Project 13","author":"Catrobat","featured_image":"http:\/\/localhost\/resources_test\/featured\/featured_2.test"},{"id":"3","name":"Project 18","author":"Catrobat","featured_image":"http:\/\/localhost\/resources_test\/featured\/featured_3.test"},{"id":"4","name":"Project 21","author":"Catrobat","featured_image":"http:\/\/localhost\/resources_test\/featured\/featured_4.test"}]');

    $client->request('GET', '/api/projects/featured', ['limit' => 2], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);

    $client->request('GET', '/api/projects/featured', ['limit' => 2, 'offset' => 2], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);

    $client->request('GET', '/api/projects/featured', ['platform' => 'android'], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);

    $client->request('GET', '/api/projects/featured', ['platform' => 'ios'], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);

    $client->request('GET', '/api/projects/featured', ['flavor' => 'luna'], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);

    $client->request('GET', '/api/projects/featured', ['max_version' => '0.985'], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);
  }
}
