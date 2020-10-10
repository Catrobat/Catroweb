<?php

namespace Tests\phpUnit\Api\User;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 * @covers \App\Api\UserApi
 */
class UsersSearchTest extends WebTestCase
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

  public function testUser(): void
  {
    $client = static::createClient();

    $client->request('GET', '/api/users/search', ['query' => 'User'], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);
  }
}
