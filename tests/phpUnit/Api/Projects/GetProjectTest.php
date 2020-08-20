<?php

namespace Tests\phpUnit\Api\Projects;

use App\Entity\Program;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 * @covers \App\Api\ProjectsApi
 */
class GetProjectTest extends WebTestCase
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

    $public_project = $this->entity_manager->getRepository(Program::class)
      ->findOneBy(['visible' => 'true'])
      ;
    $private_project = $this->entity_manager->getRepository(Program::class)
      ->findOneBy(['visible' => 'false'])
      ;

    $client->request('GET', '/api/project/'.$public_project->getId(), [], [], []);
    $this->assertResponseStatusCodeSame(406);

    $client->request('GET', '/api/project/'.$public_project->getId(), [], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);

    $client->request('GET', '/api/project/'.$private_project->getId(), [], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(200);

    $client->request('GET', '/api/project/5', [], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertResponseStatusCodeSame(404);
  }
}
