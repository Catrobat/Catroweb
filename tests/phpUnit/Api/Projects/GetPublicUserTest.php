<?php

namespace Tests\phpUnit\Api\Projects;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 * @covers \App\Api\ProjectsApi
 */
class GetPublicUserTest extends WebTestCase
{
  /**
   * {@inheritdoc}
   */
  private EntityManager $entity_manager;

  public function setUp(): void
  {
    static::$kernel = static::createKernel();
    static::$kernel->boot();
    $this->entity_manager = static::$kernel->getContainer()
      ->get('doctrine')
      ->getManager()
      ;
  }

  /**
   * {@inheritdoc}
   */
  protected function tearDown(): void
  {
    parent::tearDown();
    $this->entity_manager->close();
  }

  public function testProjects(): void
  {
    $client = static::createClient();

    $user = $this->entity_manager->getRepository(User::class)
      ->findOneBy(['username' => 'Catrobat'])
      ;
    $client->request('GET', '/api/projects/user/'.$user->getId(), [], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);

    $client->request('GET', '/api/projects/user/'.$user->getId(), ['limit' => 2], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);

    $client->request('GET', '/api/projects/user/'.$user->getId(), ['limit' => 2, 'offset' => 2], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);

    $client->request('GET', '/api/projects/user/'.$user->getId(), ['offset' => 2], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);

    $client->request('GET', '/api/projects/user/'.$user->getId(), ['max_version' => '0.984'], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);

    $client->request('GET', '/api/projects/user/'.$user->getId(), ['flavor' => 'luna'], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);
  }
}
