<?php

namespace Tests\phpUnit\Api\Projects;

use App\Api\ProjectsApi;
use App\Entity\Program;
use App\Entity\ProgramManager;
use App\Entity\User;
use App\Utils\ElapsedTimeStringFormatter;
use OpenAPI\Server\Model\ProjectResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Tests\phpUnit\TestUtils\PHPUnitUtils;

/**
 * @internal
 * @covers \App\Api\ProjectsApi
 */
class GetRecommendedTest extends TestCase
{
  /**
   * @var MockObject|ProjectsApi
   */
  private $project_api;

  private Program $program0;
  private Program $program1;
  private Program $program2;
  private Program $program3;
  private Program $program4;
  private Program $program5;
  private Program $program6;

  /**
   * @throws ReflectionException
   */
  public function setUp(): void
  {
    $this->project_api = $this->getMockBuilder(ProjectsApi::class)
      ->disableOriginalConstructor()
      ->setMethodsExcept(['projectIdRecommendationsGet'])
      ->getMock()
    ;

    $parameter_bag = $this->mockParameterBag();
    $program_manager = $this->createMock(ProgramManager::class);
    $time_formatter = $this->createMock(ElapsedTimeStringFormatter::class);

    $this->program0 = $this->mockProgram('0', 'Program 0', '1', 'Catroweb');
    $this->program1 = $this->mockProgram('1', 'Program 1', '1', 'Catroweb');
    $this->program2 = $this->mockProgram('2', 'Program 2', '1', 'Catroweb');
    $this->program3 = $this->mockProgram('3', 'Program 3', '2', 'John');
    $this->program4 = $this->mockProgram('4', 'Program 4', '2', 'John');
    $this->program5 = $this->mockProgram('5', 'Program 5', '3', 'Sara');
    $this->program6 = $this->mockProgram('6', 'Program 6', '3', 'Sara');

    $program_manager->expects($this->any())
      ->method('getProgram')
      ->willReturn([$this->program1])
    ;

    $program_manager->expects($this->any())
      ->method('getRecommendedProgramsById')
      ->willReturn([$this->program3, $this->program4])
    ;

    $program_manager->expects($this->any())
      ->method('getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgram')
      ->willReturn([$this->program5, $this->program6])
    ;

    $program_manager->expects($this->any())
      ->method('getUserPrograms')
      ->willReturn([$this->program0, $this->program2])
    ;

    $program_manager->expects($this->any())
      ->method('getPublicUserPrograms')
      ->willReturn([$this->program2])
    ;

    PHPUnitUtils::mockProperty(ProjectsApi::class, $this->project_api, 'program_manager', $program_manager);
    PHPUnitUtils::mockProperty(ProjectsApi::class, $this->project_api, 'parameter_bag', $parameter_bag);
    PHPUnitUtils::mockProperty(ProjectsApi::class, $this->project_api, 'time_formatter', $time_formatter);

    $this->mockContainer(null);
  }

  /**
   * {@inheritdoc}
   */
  protected function tearDown(): void
  {
    parent::tearDown();
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(ProjectsApi::class, $this->project_api);
  }

  /**
   * @throws ReflectionException
   */
  public function testProjectIdRecommendationsGetSimilar(): void
  {
    $projects = $this->project_api->projectIdRecommendationsGet('1', 'similar');

    $this->assertCount(2, $projects);

    $this->assertInstanceOf(ProjectResponse::class, $projects[0]);
    $this->assertInstanceOf(ProjectResponse::class, $projects[1]);

    $this->assertEquals($this->program3->getId(), $projects[0]->getId());
    $this->assertEquals($this->program3->getName(), $projects[0]->getName());
    $this->assertEquals($this->program3->getUser()->getUsername(), $projects[0]->getAuthor());

    $this->assertEquals($this->program4->getId(), $projects[1]->getId());
    $this->assertEquals($this->program4->getName(), $projects[1]->getName());
    $this->assertEquals($this->program4->getUser()->getUsername(), $projects[1]->getAuthor());
  }

  /**
   * @throws ReflectionException
   */
  public function testProjectIdRecommendationsGetAlsoDownloaded(): void
  {
    $projects = $this->project_api->projectIdRecommendationsGet('1', 'also_downloaded');

    $this->assertCount(2, $projects);

    $this->assertInstanceOf(ProjectResponse::class, $projects[0]);
    $this->assertInstanceOf(ProjectResponse::class, $projects[1]);

    $this->assertEquals($this->program5->getId(), $projects[0]->getId());
    $this->assertEquals($this->program5->getName(), $projects[0]->getName());
    $this->assertEquals($this->program5->getUser()->getUsername(), $projects[0]->getAuthor());

    $this->assertEquals($this->program6->getId(), $projects[1]->getId());
    $this->assertEquals($this->program6->getName(), $projects[1]->getName());
    $this->assertEquals($this->program6->getUser()->getUsername(), $projects[1]->getAuthor());
  }

  /**
   * @throws ReflectionException
   */
  public function testProjectIdRecommendationsGetMoreFromThisUserNotLoggedIn(): void
  {
    $projects = $this->project_api->projectIdRecommendationsGet('1', 'more_from_user');

    $this->assertCount(1, $projects);

    $this->assertInstanceOf(ProjectResponse::class, $projects[0]);

    $this->assertEquals($this->program2->getId(), $projects[0]->getId());
    $this->assertEquals($this->program2->getName(), $projects[0]->getName());
    $this->assertEquals($this->program2->getUser()->getUsername(), $projects[0]->getAuthor());
  }

  /**
   * @throws ReflectionException
   */
  public function testProjectIdRecommendationsGetMoreFromThisUserLoggedIn(): void
  {
    $this->mockContainer($this->mockUser('1', 'Catroweb'));

    $projects = $this->project_api->projectIdRecommendationsGet('1', 'more_from_user');

    $this->assertCount(2, $projects);

    $this->assertInstanceOf(ProjectResponse::class, $projects[0]);
    $this->assertInstanceOf(ProjectResponse::class, $projects[1]);

    $this->assertEquals($this->program0->getId(), $projects[0]->getId());
    $this->assertEquals($this->program0->getName(), $projects[0]->getName());
    $this->assertEquals($this->program0->getUser()->getUsername(), $projects[0]->getAuthor());

    $this->assertEquals($this->program2->getId(), $projects[1]->getId());
    $this->assertEquals($this->program2->getName(), $projects[1]->getName());
    $this->assertEquals($this->program2->getUser()->getUsername(), $projects[1]->getAuthor());
  }

  /**
   * @throws ReflectionException
   */
  private function mockContainer(?User $user): void
  {
    $container = new Container();
    $container->set('router', $this->mockRouter());
    $container->set('security.token_storage', $this->mockTokenStorage($user));
    PHPUnitUtils::mockProperty(ProjectsApi::class, $this->project_api, 'container', $container);
  }

  /**
   * @return RouterInterface|MockObject
   */
  private function mockRouter()
  {
    $router = $this->getMockBuilder(RouterInterface::class)->getMock();
    $router->expects($this->any())->method('generate')->willReturn('http://localhost:8080/app/project/6a306940-4445-11eb-9fc1-0242ac120004');

    return $router;
  }

  /**
   * @return TokenStorageInterface|MockObject
   */
  private function mockTokenStorage(?User $user)
  {
    $token_storage_interface = $this->createMock(TokenStorageInterface::class);
    if (null === $user) {
      $token_storage_interface->expects($this->any())->method('getToken')->willReturn(null);
    } else {
      $token_interface = $this->createMock(TokenInterface::class);
      $token_interface->expects($this->any())->method('getUser')->willReturn($user);
      $token_storage_interface->expects($this->any())->method('getToken')->willReturn($token_interface);
    }

    return $token_storage_interface;
  }

  /**
   * @return Program|MockObject
   */
  private function mockProgram(string $id, string $name, string $userId, string $username)
  {
    $project = $this->createMock(Program::class);

    $project->expects($this->any())->method('getId')->willReturn($id);
    $project->expects($this->any())->method('getName')->willReturn($name);

    $user = $this->mockUser($userId, $username);
    $project->expects($this->any())->method('getUser')->willReturn($user);

    return $project;
  }

  /**
   * @return User|MockObject
   */
  private function mockUser(string $userId, string $username)
  {
    $user = $this->createMock(User::class);
    $user->expects($this->any())->method('getId')->willReturn($userId);
    $user->expects($this->any())->method('getUsername')->willReturn($username);

    return $user;
  }

  /**
   * @return MockObject|ParameterBagInterface
   */
  private function mockParameterBag()
  {
    $parameter_bag = $this->getMockBuilder(ParameterBagInterface::class)
      ->disableOriginalConstructor()
      ->setMethodsExcept(['get'])
      ->getMockForAbstractClass()
    ;

    $parameter_bag
      ->expects($this->any())
      ->method('get')
      ->will(
        $this->returnCallback(
          function ($param) {
            switch ($param) {
              case 'umbrellaTheme':
                return 'app';
            }

            return '';
          }
        )
      )
    ;

    return $parameter_bag;
  }
}
