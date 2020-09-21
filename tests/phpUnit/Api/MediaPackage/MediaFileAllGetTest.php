<?php

namespace Tests\phpUnit\Api\MediaPackage;

use App\Entity\ExampleProgram;
use App\Entity\FeaturedProgram;
use App\Entity\Flavor;
use App\Entity\MediaPackage;
use App\Entity\MediaPackageCategory;
use App\Entity\MediaPackageFile;
use App\Entity\Program;
use App\Entity\User;
use Datetime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @internal
 * @covers \App\Api\MediaLibraryApi
 */
class MediaFileAllGetTest extends WebTestCase
{
  /**
   * {@inheritdoc}
   */
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

    $catrobat = $this->addUser('Catrobat', 'CatrobatUser@localhost.at', '123456', 'user-catrobat');
    $user1 = $this->addUser('User 1', 'user1@localhost.at', '123456', 'user-1');
    $user2 = $this->addUser('User 2', 'user2@localhost.at', '123456', 'user-2');
    $user3 = $this->addUser('User 3', 'user3@localhost.at', '123456', 'user-3');

    $this->addProject('Project 1', '1', $catrobat, 10, new \DateTime(), 1048576, 0, '0.999', 'pocketcode', true, false);
    $this->addProject('Project 2', '2', $catrobat, 10, new \DateTime(), 1048576, 0, '0.999', 'pocketcode', true, false);
    $this->addFeaturedProject('Project 3', '3', $catrobat, 10, new \DateTime(), 1048576, 0, '0.999', 'pocketcode', false, true);
    $this->addProject('Project 4', '4', $user1, 10, new \DateTime(), 1048576, 0, '0.999', 'pocketcode', true, false);
    $this->addProject('Project 5', '5', $catrobat, 10, new \DateTime(), 1048576, 0, '0.999', 'pocketcode', true, false);
    $this->addProject('Project 6', '6', $catrobat, 10, new \DateTime(), 1048576, 0, '0.999', 'pocketcode', false, true);
    $this->addProject('Project 7', '7', $catrobat, 10, new \DateTime(), 1048576, 0, '0.999', 'pocketcode', false, true);
    $this->addProject('Project 8', '8', $catrobat, 10, new \DateTime(), 1048576, 0, '0.999', 'pocketcode', true, false);
    $this->addProject('Project 9', '9', $catrobat, 10, new \DateTime(), 1048576, 0, '0.999', 'luna', false, true);
    $this->addProject('Project 10', '10', $catrobat, 40, new \DateTime(), 1048576, 0, '0.999', 'pocketcode', false, true);
    $this->addProject('Project 11', '11', $user1, 10, new \DateTime(), 1048576, 0, '0.999', 'pocketcode', true, false);
    $this->addProject('Project 12', '12', $catrobat, 10, new \DateTime(), 1048576, 0, '0.999', 'luna', true, false);
    $this->addFeaturedProject('Project 13', '13', $catrobat, 60, new \DateTime(), 1048576, 0, '0.123', 'pocketcode', false, true);
    $this->addProject('Project 14', '14', $user1, 10, new \DateTime(), 1048576, 0, '0.999', 'luna', true, false);
    $this->addExampleProject('Project 15', '15', $catrobat, 10, new \DateTime(), 1048576, 0, '0.999', 'pocketcode', false, true);
    $this->addProject('Project 16', '16', $user3, 50, new \DateTime(), 1048576, 0, '0.999', 'pocketcode', true, false);
    $this->addProject('Project 17', '17', $catrobat, 10, new \DateTime(), 1048576, 0, '0.999', 'luna', true, false);
    $this->addFeaturedProject('Project 18', '18', $catrobat, 10, new \DateTime(), 1048576, 0, '0.999', 'pocketcode', false, true);
    $this->addProject('Project 19', '19', $catrobat, 90, new \DateTime(), 1048576, 0, '0.985', 'pocketcode', true, false);
    $this->addProject('Project 20', '20', $user2, 10, new \DateTime(), 1048576, 0, '0.999', 'arduino', true, false);
    $this->addFeaturedProject('Project 21', '21', $catrobat, 10, new \DateTime(), 1048576, 0, '0.985', 'pocketcode', false, true);

    $looks_media_package = $this->addMediaPackage('Looks', 'looks');
    $sounds_media_package = $this->addMediaPackage('Sounds', 'sounds');
    $empty_media_package = $this->addMediaPackage('Empty', 'empty');

    $sounds_media_package_cat = $this->addMediaPackageCategory('Sounds Family', $sounds_media_package);
    $looks_media_package_cat = $this->addMediaPackageCategory('Looks Family', $looks_media_package);

    $luna = $this->addFlavor('luna');
    $pocket_code = $this->addFlavor('pocketcode');

    $this->addMediaFile('Panda 1', $file, $looks_media_package_cat, $pocket_code, 'Catrobat');
    $this->addMediaFile('Cat', $file, $looks_media_package_cat, $luna, 'CatrobatLuna');
    $this->addMediaFile('Dog', $file, $looks_media_package_cat, $pocket_code, 'Catrobat');
    $this->addMediaFile('Rabbit', $file, $looks_media_package_cat, $luna, 'CatrobatLuna');
    $this->addMediaFile('Bear', $file, $looks_media_package_cat, $pocket_code, 'Catrobat');
    $this->addMediaFile('Snake', $file, $sounds_media_package_cat, $luna, 'CatrobatLuna');
    $this->addMediaFile('Panda 2', $file, $looks_media_package_cat, $pocket_code, 'Catrobat');
  }

  /**
   * {@inheritdoc}
   */
  protected function tearDown(): void
  {
    parent::tearDown();
    $this->entity_manager->close();
  }

  public function testMedia(): void
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

    $client->request('GET', '/api/media/files', [], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);
    $data = $client->getResponse()->getContent();
    $this->assertJsonStringEqualsJsonString($data, '[{"id":1,"name":"Panda 1","flavor":"pocketcode","package":"Looks","category":"Looks Family","author":"Catrobat","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/1"},{"id":2,"name":"Cat","flavor":"luna","package":"Looks","category":"Looks Family","author":"CatrobatLuna","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/2"},{"id":3,"name":"Dog","flavor":"pocketcode","package":"Looks","category":"Looks Family","author":"Catrobat","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/3"},{"id":4,"name":"Rabbit","flavor":"luna","package":"Looks","category":"Looks Family","author":"CatrobatLuna","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/4"},{"id":5,"name":"Bear","flavor":"pocketcode","package":"Looks","category":"Looks Family","author":"Catrobat","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/5"},{"id":6,"name":"Snake","flavor":"luna","package":"Sounds","category":"Sounds Family","author":"CatrobatLuna","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/6"}, {"id":7,"name":"Panda 2","flavor":"pocketcode","package":"Looks","category":"Looks Family","author":"Catrobat","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/7"}]');

    $client->request('GET', '/api/media/files', ['flavor' => 'luna'], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);
    $data = $client->getResponse()->getContent();
    $this->assertJsonStringEqualsJsonString($data, '[{"id":2,"name":"Cat","flavor":"luna","package":"Looks","category":"Looks Family","author":"CatrobatLuna","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/2"},{"id":4,"name":"Rabbit","flavor":"luna","package":"Looks","category":"Looks Family","author":"CatrobatLuna","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/4"},{"id":6,"name":"Snake","flavor":"luna","package":"Sounds","category":"Sounds Family","author":"CatrobatLuna","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/6"}]');

    $client->request('GET', '/api/media/files', ['limit' => 3], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);
    $data = $client->getResponse()->getContent();
    $this->assertJsonStringEqualsJsonString($data, '[{"id":1,"name":"Panda 1","flavor":"pocketcode","package":"Looks","category":"Looks Family","author":"Catrobat","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/1"},{"id":2,"name":"Cat","flavor":"luna","package":"Looks","category":"Looks Family","author":"CatrobatLuna","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/2"},{"id":3,"name":"Dog","flavor":"pocketcode","package":"Looks","category":"Looks Family","author":"Catrobat","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/3"}]');

    $client->request('GET', '/api/media/files', ['offset' => 3], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);
    $data = $client->getResponse()->getContent();
    $this->assertJsonStringEqualsJsonString($data, '[{"id":4,"name":"Rabbit","flavor":"luna","package":"Looks","category":"Looks Family","author":"CatrobatLuna","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/4"},{"id":5,"name":"Bear","flavor":"pocketcode","package":"Looks","category":"Looks Family","author":"Catrobat","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/5"},{"id":6,"name":"Snake","flavor":"luna","package":"Sounds","category":"Sounds Family","author":"CatrobatLuna","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/6"}, {"id":7,"name":"Panda 2","flavor":"pocketcode","package":"Looks","category":"Looks Family","author":"Catrobat","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/7"}]');

    $client->request('GET', '/api/media/files', ['offset' => 2, 'limit' => 2], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);
    $data = $client->getResponse()->getContent();
    $this->assertJsonStringEqualsJsonString($data, '[{"id":3,"name":"Dog","flavor":"pocketcode","package":"Looks","category":"Looks Family","author":"Catrobat","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/3"},{"id":4,"name":"Rabbit","flavor":"luna","package":"Looks","category":"Looks Family","author":"CatrobatLuna","extension":"","download_url":"http:\/\/localhost\/app\/download-media\/4"}]');
  }

  public function addMediaFile(string $name, File $file, MediaPackageCategory $media_package_cat, Flavor $flavor, string $author): void
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

  public function addUser(string $username, string $email, string $password, string $id): User
  {
    $user = new User();
    $user->setUsername($username);
    $user->setEmail($email);
    $user->setPlainPassword($password);
    $user->setEnabled(true);
    $user->setId($id);
    $this->entity_manager->persist($user);
    $this->entity_manager->flush();

    return $user;
  }

  public function addProject(string $name, string $id, User $user, int $views, DateTime $uploaded, int $file_size, int $version, string $language, string $flavor, bool $private, bool $visible): Program
  {
    $project = new Program();
    $project->setId($id);
    $project->setName($name);
    $project->setUser($user);
    $project->setViews($views);
    $project->setUploadedAt($uploaded);
    $project->setFilesize($file_size);
    $project->setVersion($version);
    $project->setLanguageVersion($language);
    $project->setFlavor($flavor);
    $project->setPrivate($private);
    $project->setVisible($visible);
    $this->entity_manager->persist($project);
    $this->entity_manager->flush();

    return $project;
  }

  public function addFeaturedProject(string $name, string $id, User $user, int $views, DateTime $uploaded, int $file_size, int $version, string $language, string $flavor, bool $private, bool $visible): void
  {
    $featured_project = new FeaturedProgram();
    $project = $this->addProject($name, $id, $user, $views, $uploaded, $file_size, $version, $language, $flavor, $private, $visible);
    $featured_project->setProgram($project);
    $featured_project->setImageType('test');
    $this->entity_manager->persist($featured_project);
    $this->entity_manager->flush();
  }

  public function addExampleProject(string $name, string $id, User $user, int $views, DateTime $uploaded, int $file_size, int $version, string $language, string $flavor, bool $private, bool $visible): void
  {
    $featured_project = new ExampleProgram();
    $project = $this->addProject($name, $id, $user, $views, $uploaded, $file_size, $version, $language, $flavor, $private, $visible);
    $featured_project->setProgram($project);
    $featured_project->setImageType('test');
    $this->entity_manager->persist($featured_project);
    $this->entity_manager->flush();
  }

  public function addMediaPackage(string $name, string $name_url): MediaPackage
  {
    $media_package = new MediaPackage();
    $media_package->setName($name);
    $media_package->setNameUrl($name_url);
    $this->entity_manager->persist($media_package);
    $this->entity_manager->flush();

    return $media_package;
  }

  public function addMediaPackageCategory(string $name, MediaPackage $media_package): MediaPackageCategory
  {
    $media_package_cat = new MediaPackageCategory();
    $media_package_cat->setName($name);
    $media_package_cat->setPackage(new ArrayCollection([$media_package]));
    $this->entity_manager->persist($media_package_cat);

    return $media_package_cat;
  }

  public function addFlavor(string $name): Flavor
  {
    $flavor = new Flavor();
    $flavor->setName($name);
    $this->entity_manager->persist($flavor);
    $this->entity_manager->flush();

    return $flavor;
  }
}
