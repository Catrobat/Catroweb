<?php

namespace Tests\Api\MediaPackage;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 * @covers \App\Api\MediaLibraryApi
 */
class MediaFileSearchGetTest extends WebTestCase
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

    $client->request('GET', '/api/media/files/search', [], [], ['HTTP_ACCEPT' => 'text/html']);
    $this->assertResponseStatusCodeSame(406);

    $client->request('GET', '/api/media/files/search', [], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(400);

    $client->request('GET', '/api/media/files/search', ['limit' => 'a'], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(400);

    $client->request('GET', '/api/media/files/search', ['query' => 'Panda'], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);
    $data = $client->getResponse()->getContent();
    $this->assertJsonStringEqualsJsonString($data, '[{"id":1,"name":"Panda 1","flavor":"pocketcode","package":"Looks","category":"Looks Family","author":"Catrobat","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/1"},{"id":7,"name":"Panda 2","flavor":"pocketcode","package":"Looks","category":"Looks Family","author":"Catrobat","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/7"}]');

    $client->request('GET', '/api/media/files/search', ['query' => 'a', 'flavor' => 'luna'], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);
    $data = $client->getResponse()->getContent();
    $this->assertJsonStringEqualsJsonString($data, '[{"id":5,"name":"Bear","flavor":"pocketcode","package":"Looks","category":"Looks Family","author":"Catrobat","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/5"},{"id":2,"name":"Cat","flavor":"luna","package":"Looks","category":"Looks Family","author":"CatrobatLuna","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/2"},{"id":1,"name":"Panda 1","flavor":"pocketcode","package":"Looks","category":"Looks Family","author":"Catrobat","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/1"},{"id":7,"name":"Panda 2","flavor":"pocketcode","package":"Looks","category":"Looks Family","author":"Catrobat","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/7"},{"id":4,"name":"Rabbit","flavor":"luna","package":"Looks","category":"Looks Family","author":"CatrobatLuna","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/4"},{"id":6,"name":"Snake","flavor":"luna","package":"Sounds","category":"Sounds Family","author":"CatrobatLuna","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/6"}]');

    $client->request('GET', '/api/media/files/search', ['query' => 'Dog'], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);
    $data = $client->getResponse()->getContent();
    $this->assertJsonStringEqualsJsonString($data, '[{"id":3,"name":"Dog","flavor":"pocketcode","package":"Looks","category":"Looks Family","author":"Catrobat","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/3"}]');

    $client->request('GET', '/api/media/files/search', ['query' => 'Elephant'], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);
    $data = $client->getResponse()->getContent();
    $this->assertJsonStringEqualsJsonString($data, '[]');

    $client->request('GET', '/api/media/files/search', ['query' => 'Snake', 'flavor' => 'luna'], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);
    $data = $client->getResponse()->getContent();
    $this->assertJsonStringEqualsJsonString($data, '[{"id":6,"name":"Snake","flavor":"luna","package":"Sounds","category":"Sounds Family","author":"CatrobatLuna","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/6"}]');

    $client->request('GET', '/api/media/files/search', ['query' => 'Dog', 'package_name' => 'Sounds'], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);
    $data = $client->getResponse()->getContent();
    $this->assertJsonStringEqualsJsonString($data, '[]');
  }
}
