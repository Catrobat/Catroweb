<?php

namespace Tests\Api\MediaPackage;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 * @covers \App\Api\MediaLibraryApi
 */
class MediaFileIdGetTest extends WebTestCase
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

  public function testMedia(): void
  {
    $client = static::createClient();

    $client->request('GET', '/api/media/file/5', [], [], ['HTTP_ACCEPT' => 'text/html']);
    $this->assertResponseStatusCodeSame(406);

    $client->request('GET', '/api/media/file/a', [], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(404);

    $client->request('GET', '/api/media/file/500000', [], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(404);

    $client->request('GET', '/api/media/file/1', [], [], ['HTTP_ACCEPT' => 'application/json']);
    $data = $client->getResponse()->getContent();
    $this->assertResponseStatusCodeSame(200);
    $this->assertJsonStringEqualsJsonString($data, '{"id":1,"name":"Panda 1","flavor":"pocketcode","package":"Looks","category":"Looks Family","author":"Catrobat","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/1"}');

    $client->request('GET', '/api/media/file/5', [], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);
    $data = $client->getResponse()->getContent();
    $this->assertJsonStringEqualsJsonString($data, '{"id":5,"name":"Bear","flavor":"pocketcode","package":"Looks","category":"Looks Family","author":"Catrobat","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/5"}');
  }
}
