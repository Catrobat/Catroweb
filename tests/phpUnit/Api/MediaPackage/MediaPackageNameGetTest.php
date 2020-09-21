<?php

namespace Tests\Api\MediaPackage;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 * @covers \App\Api\MediaLibraryApi
 */
class MediaPackageNameGetTest extends WebTestCase
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

    $client->request('GET', '/api/media/package/looks', [], [], ['HTTP_ACCEPT' => 'text/html']);
    $this->assertResponseStatusCodeSame(406);

    $client->request('GET', '/api/media/package/looksssss', [], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(404);

    $client->request('GET', '/api/media/package/looks', [], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);
    $data = $client->getResponse()->getContent();
    $this->assertJsonStringEqualsJsonString($data, '[{"id":1,"name":"Panda 1","flavor":"pocketcode","package":"Looks","category":"Looks Family","author":"Catrobat","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/1"},{"id":2,"name":"Cat","flavor":"luna","package":"Looks","category":"Looks Family","author":"CatrobatLuna","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/2"},{"id":3,"name":"Dog","flavor":"pocketcode","package":"Looks","category":"Looks Family","author":"Catrobat","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/3"},{"id":4,"name":"Rabbit","flavor":"luna","package":"Looks","category":"Looks Family","author":"CatrobatLuna","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/4"},{"id":5,"name":"Bear","flavor":"pocketcode","package":"Looks","category":"Looks Family","author":"Catrobat","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/5"}, {"id":7,"name":"Panda 2","flavor":"pocketcode","package":"Looks","category":"Looks Family","author":"Catrobat","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/7"}]');

    $client->request('GET', '/api/media/package/looks', ['limit' => 1], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);
    $data = $client->getResponse()->getContent();
    $this->assertJsonStringEqualsJsonString($data, '[{"id":1,"name":"Panda 1","flavor":"pocketcode","package":"Looks","category":"Looks Family","author":"Catrobat","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/1"}]');

    $client->request('GET', '/api/media/package/looks', ['limit' => 1, 'offset' => 3], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);
    $data = $client->getResponse()->getContent();
    $this->assertJsonStringEqualsJsonString($data, '[{"id":4,"name":"Rabbit","flavor":"luna","package":"Looks","category":"Looks Family","author":"CatrobatLuna","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/4"}]');

    $client->request('GET', '/api/media/package/looks', ['offset' => 5], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);
    $data = $client->getResponse()->getContent();
    $this->assertJsonStringEqualsJsonString($data, '[{"id":7,"name":"Panda 2","flavor":"pocketcode","package":"Looks","category":"Looks Family","author":"Catrobat","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/7"}]');

    $client->request('GET', '/api/media/package/empty', [], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);
    $data = $client->getResponse()->getContent();
    $this->assertJsonStringEqualsJsonString($data, '[]');
  }
}
