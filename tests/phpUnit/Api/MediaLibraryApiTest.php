<?php

namespace Tests\Api;

use App\Entity\Flavor;
use App\Entity\MediaPackage;
use App\Entity\MediaPackageCategory;
use App\Entity\MediaPackageFile;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @covers \App\Api\MediaLibraryApi
 *
 * @internal
 */
class MediaLibraryApiTest extends WebTestCase
{
  private EntityManager $entity_manager;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void
  {
    static::$kernel = static::createKernel();
    static::$kernel->boot();
    $this->entity_manager = static::$kernel->getContainer()
      ->get('doctrine')
      ->getManager()
      ;

    fopen('/tmp/phpUnitTest', 'w');
    $file = new File('/tmp/phpUnitTest');

    $luna = $this->createFlavors('luna');
    $pocket_code = $this->createFlavors('pocketcode');

    $new_media_package = new MediaPackage();
    $new_media_package->setName('Looks');
    $new_media_package->setNameUrl('looks');
    $this->entity_manager->persist($new_media_package);
    $this->entity_manager->flush();

    $new_media_package_cat = new MediaPackageCategory();
    $new_media_package_cat->setName('Pocket Family');
    $new_media_package_cat->setPackage(new ArrayCollection([$new_media_package]));
    $this->entity_manager->persist($new_media_package_cat);
    $this->entity_manager->flush();

    $this->addMediaFile('Panda', $file, $new_media_package_cat, $pocket_code, 'Catrobat');
    $this->addMediaFile('Cat', $file, $new_media_package_cat, $luna, 'CatrobatLuna');
    $this->addMediaFile('Dog', $file, $new_media_package_cat, $pocket_code, 'Catrobat');
    $this->addMediaFile('Rabbit', $file, $new_media_package_cat, $luna, 'CatrobatLuna');
    $this->addMediaFile('Bear', $file, $new_media_package_cat, $pocket_code, 'Catrobat');
    $this->addMediaFile('Snake', $file, $new_media_package_cat, $luna, 'CatrobatLuna');
  }

  /**
   * {@inheritdoc}
   */
  protected function tearDown(): void
  {
    parent::tearDown();
    $this->entity_manager->close();
  }

  public function testMediaLibraryAPI(): void
  {
    $client = static::createClient();

    $client->request('GET', '/api/media/files', [], [], ['HTTP_ACCEPT' => 'text/html']);
    $this->assertResponseStatusCodeSame(406);

    $client->request('GET', '/api/media/files', ['limit' => 'a'], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(400);

    $client->request('GET', '/api/media/files', ['offset' => 'a'], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(400);

    $client->request('GET', '/api/media/files', ['flavor' => 100], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(400);

    $client->request('GET', '/api/media/file/5', [], [], ['HTTP_ACCEPT' => 'text/html']);
    $this->assertResponseStatusCodeSame(406);

    $client->request('GET', '/api/media/file/a', [], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(404);

    $client->request('GET', '/api/media/file/500000', [], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(404);

    $client->request('GET', '/api/media/file/1', [], [], ['HTTP_ACCEPT' => 'application/json']);
    $data = $client->getResponse()->getContent();
    $this->assertResponseStatusCodeSame(200);
    $this->assertJsonStringEqualsJsonString($data, '{"id":1,"name":"Panda","flavor":"pocketcode","package":"Looks","category":"Pocket Family","author":"Catrobat","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/1"}');

    $client->request('GET', '/api/media/file/5', [], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);
    $data = $client->getResponse()->getContent();
    $this->assertJsonStringEqualsJsonString($data, '{"id":5,"name":"Bear","flavor":"pocketcode","package":"Looks","category":"Pocket Family","author":"Catrobat","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/5"}');

    $client->request('GET', '/api/media/files', [], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);
    $data = $client->getResponse()->getContent();
    $this->assertJsonStringEqualsJsonString($data, '[{"id":1,"name":"Panda","flavor":"pocketcode","package":"Looks","category":"Pocket Family","author":"Catrobat","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/1"},{"id":2,"name":"Cat","flavor":"luna","package":"Looks","category":"Pocket Family","author":"CatrobatLuna","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/2"},{"id":3,"name":"Dog","flavor":"pocketcode","package":"Looks","category":"Pocket Family","author":"Catrobat","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/3"},{"id":4,"name":"Rabbit","flavor":"luna","package":"Looks","category":"Pocket Family","author":"CatrobatLuna","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/4"},{"id":5,"name":"Bear","flavor":"pocketcode","package":"Looks","category":"Pocket Family","author":"Catrobat","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/5"},{"id":6,"name":"Snake","flavor":"luna","package":"Looks","category":"Pocket Family","author":"CatrobatLuna","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/6"}]');

    $client->request('GET', '/api/media/files', ['flavor' => 'luna'], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);
    $data = $client->getResponse()->getContent();
    $this->assertJsonStringEqualsJsonString($data, '[{"id":2,"name":"Cat","flavor":"luna","package":"Looks","category":"Pocket Family","author":"CatrobatLuna","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/2"},{"id":4,"name":"Rabbit","flavor":"luna","package":"Looks","category":"Pocket Family","author":"CatrobatLuna","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/4"},{"id":6,"name":"Snake","flavor":"luna","package":"Looks","category":"Pocket Family","author":"CatrobatLuna","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/6"}]');

    $client->request('GET', '/api/media/files', ['limit' => 3], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);
    $data = $client->getResponse()->getContent();
    $this->assertJsonStringEqualsJsonString($data, '[{"id":1,"name":"Panda","flavor":"pocketcode","package":"Looks","category":"Pocket Family","author":"Catrobat","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/1"},{"id":2,"name":"Cat","flavor":"luna","package":"Looks","category":"Pocket Family","author":"CatrobatLuna","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/2"},{"id":3,"name":"Dog","flavor":"pocketcode","package":"Looks","category":"Pocket Family","author":"Catrobat","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/3"}]');

    $client->request('GET', '/api/media/files', ['offset' => 3], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);
    $data = $client->getResponse()->getContent();
    $this->assertJsonStringEqualsJsonString($data, '[{"id":4,"name":"Rabbit","flavor":"luna","package":"Looks","category":"Pocket Family","author":"CatrobatLuna","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/4"},{"id":5,"name":"Bear","flavor":"pocketcode","package":"Looks","category":"Pocket Family","author":"Catrobat","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/5"},{"id":6,"name":"Snake","flavor":"luna","package":"Looks","category":"Pocket Family","author":"CatrobatLuna","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/6"}]');

    $client->request('GET', '/api/media/files', ['offset' => 2, 'limit' => 2], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);
    $data = $client->getResponse()->getContent();
    $this->assertJsonStringEqualsJsonString($data, '[{"id":3,"name":"Dog","flavor":"pocketcode","package":"Looks","category":"Pocket Family","author":"Catrobat","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/3"},{"id":4,"name":"Rabbit","flavor":"luna","package":"Looks","category":"Pocket Family","author":"CatrobatLuna","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/4"}]');
  }

  private function addMediaFile(string $name, File $file, MediaPackageCategory $media_package_cat, Flavor $flavor, string $author): void
  {
    $new_media_package_file = new MediaPackageFile();
    $new_media_package_file->setName($name);
    $new_media_package_file->setFile($file);
    $new_media_package_file->setCategory($media_package_cat);
    $new_media_package_file->addFlavor($flavor);
    $new_media_package_file->setAuthor($author);
    $new_media_package_file->setExtension($file->getExtension());

    $this->entity_manager->persist($new_media_package_file);
    $this->entity_manager->flush();
  }

  private function createFlavors(string $name, bool $flush = false): Flavor
  {
    $flavor = new Flavor();
    $flavor->setName($name);

    $this->entity_manager->persist($flavor);

    if ($flush)
    {
      $this->entity_manager->flush();
    }

    return $flavor;
  }
}
