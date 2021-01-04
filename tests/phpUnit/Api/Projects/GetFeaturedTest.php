<?php

namespace Tests\phpUnit\Api\Projects;

use App\Api\ProjectsApi;
use App\Catrobat\Services\ImageRepository;
use App\Entity\FeaturedProgram;
use App\Entity\Program;
use App\Entity\User;
use App\Repository\FeaturedRepository;
use OpenAPI\Server\Model\FeaturedProjectResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Tests\phpUnit\TestUtils\PHPUnitUtils;

/**
 * @internal
 * @covers \App\Api\ProjectsApi
 */
class GetFeaturedTest extends TestCase
{
  /**
   * @var MockObject|ProjectsApi
   */
  private $project_api;

  private FeaturedProgram $featured_project1;
  private FeaturedProgram $featured_project2;

  /**
   * @throws ReflectionException
   */
  public function setUp(): void
  {
    $this->project_api = $this->getMockBuilder(ProjectsApi::class)
      ->disableOriginalConstructor()
      ->setMethodsExcept(['projectsFeaturedGet'])
      ->getMock()
    ;
    $parameter_bag = $this->mockParameterBag();
    $featured_repository = $this->createMock(FeaturedRepository::class);
    $image_repository = $this->createMock(ImageRepository::class);

    $this->featured_project1 = $this->mockFeaturedProgram(
      1,
      $this->mockProgram('777', 'Tic-Tac-Toe', 'Catroweb')
    );

    $this->featured_project2 = $this->mockFeaturedProgram(
      2,
      $this->mockProgram('Abc-123', 'Pacman', 'Steve')
    );

    $featured_repository->expects($this->any())
      ->method('getFeaturedPrograms')
      ->willReturn([$this->featured_project1, $this->featured_project2])
    ;

    $image_repository->expects($this->any())
      ->method('getAbsoluteWebPath')
      ->willReturn('http://localhost:8080/resources/featured/featured.png')
    ;

    $router = $this->getMockBuilder('Symfony\Component\Routing\RouterInterface')->getMock();
    $router->expects($this->any())->method('generate')->willReturn('http://localhost:8080/app/project/6a306940-4445-11eb-9fc1-0242ac120004');

    $container = new Container();
    $container->set('router', $router);

    PHPUnitUtils::mockProperty(ProjectsApi::class, $this->project_api, 'featured_repository', $featured_repository);
    PHPUnitUtils::mockProperty(ProjectsApi::class, $this->project_api, 'parameter_bag', $parameter_bag);
    PHPUnitUtils::mockProperty(ProjectsApi::class, $this->project_api, 'container', $container);
    PHPUnitUtils::mockProperty(ProjectsApi::class, $this->project_api, 'image_repository', $image_repository);
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
  public function testProjectsFeaturedGet(): void
  {
    $featured_project_response1 = $this->mockFeaturedProgramResponse($this->featured_project1);
    $featured_project_response2 = $this->mockFeaturedProgramResponse($this->featured_project2);

    $featured_projects = $this->project_api->projectsFeaturedGet();

    $this->assertJsonStringEqualsJsonString(
      json_encode($featured_projects),
      json_encode([$featured_project_response1, $featured_project_response2])
    );
  }

  /**
   * @param MockObject|Program $program
   *
   * @return FeaturedProgram|MockObject
   */
  private function mockFeaturedProgram(int $id, $program)
  {
    $featured_project = $this->createMock(FeaturedProgram::class);

    $featured_project->expects($this->any())->method('getId')->willReturn($id);
    $featured_project->expects($this->any())->method('getProgram')->willReturn($program);
    $featured_project->expects($this->any())->method('getImageType')->willReturn('png');

    return $featured_project;
  }

  /**
   * @param MockObject|FeaturedProgram $featured_program
   *
   * @throws ReflectionException
   *
   * @return FeaturedProjectResponse|MockObject
   */
  private function mockFeaturedProgramResponse($featured_program)
  {
    $featured_project_response = $this->createMock(FeaturedProjectResponse::class);

    PHPUnitUtils::mockProperty(FeaturedProjectResponse::class, $featured_project_response, 'id', $featured_program->getId());
    PHPUnitUtils::mockProperty(FeaturedProjectResponse::class, $featured_project_response, 'project_id', $featured_program->getProgram()->getId());
    PHPUnitUtils::mockProperty(FeaturedProjectResponse::class, $featured_project_response, 'project_url', 'http://localhost:8080/app/project/6a306940-4445-11eb-9fc1-0242ac120004');
    PHPUnitUtils::mockProperty(FeaturedProjectResponse::class, $featured_project_response, 'name', $featured_program->getProgram()->getName());
    PHPUnitUtils::mockProperty(FeaturedProjectResponse::class, $featured_project_response, 'author', $featured_program->getProgram()->getUser()->getUsername());
    PHPUnitUtils::mockProperty(FeaturedProjectResponse::class, $featured_project_response, 'featured_image', 'http://localhost:8080/resources/featured/featured.png');

    return $featured_project_response;
  }

  /**
   * @return Program|MockObject
   */
  private function mockProgram(string $id, string $name, string $username)
  {
    $project = $this->createMock(Program::class);

    $project->expects($this->any())->method('getId')->willReturn($id);
    $project->expects($this->any())->method('getName')->willReturn($name);

    $user = $this->createMock(User::class);
    $user->expects($this->any())->method('getUsername')->willReturn($username);

    $project->expects($this->any())->method('getUser')->willReturn($user);

    return $project;
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
          function ($param)
          {
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
