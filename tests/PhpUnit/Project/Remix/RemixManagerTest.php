<?php

namespace Tests\PhpUnit\Project\Remix;

use App\DB\Entity\Project\Project;
use App\DB\Entity\Project\Remix\ProjectRemixRelation;
use App\DB\Entity\Project\Scratch\ScratchProject;
use App\DB\Entity\Project\Scratch\ScratchProjectRemixRelation;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Project\ProjectRemixBackwardRepository;
use App\DB\EntityRepository\Project\ProjectRemixRepository;
use App\DB\EntityRepository\Project\ProjectRepository;
use App\DB\EntityRepository\Project\ScratchProjectRemixRepository;
use App\DB\EntityRepository\Project\ScratchProjectRepository;
use App\Project\Remix\RemixData;
use App\Project\Remix\RemixGraphManipulator;
use App\Project\Remix\RemixManager;
use App\User\Notification\NotificationManager;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \App\Project\Remix\RemixManager
 */
class RemixManagerTest extends TestCase
{
  private RemixManager $remix_manager;

  private EntityManager|MockObject $entity_manager;

  private MockObject|ProjectRepository $project_repository;

  private MockObject|ScratchProjectRepository $scratch_project_repository;

  private MockObject|ProjectRemixRepository $project_remix_repository;

  protected function setUp(): void
  {
    $this->entity_manager = $this->createMock(EntityManager::class);
    $this->project_repository = $this->createMock(ProjectRepository::class);
    $this->scratch_project_repository = $this->createMock(ScratchProjectRepository::class);
    $this->project_remix_repository = $this->createMock(ProjectRemixRepository::class);
    $project_remix_backward_repository = $this->createMock(ProjectRemixBackwardRepository::class);
    $scratch_project_remix_repository = $this->createMock(ScratchProjectRemixRepository::class);
    $remix_graph_manipulator = $this->createMock(RemixGraphManipulator::class);
    $catro_notification_service = $this->createMock(NotificationManager::class);
    $this->remix_manager = new RemixManager($this->entity_manager, $this->project_repository, $this->scratch_project_repository, $this->project_remix_repository, $project_remix_backward_repository, $scratch_project_remix_repository, $remix_graph_manipulator, $catro_notification_service);
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(RemixManager::class, $this->remix_manager);
  }

  /**
   * @throws \Exception
   */
  public function testAddSingleScratchProject(): void
  {
    $expected_id_of_first_project = '123';
    $expected_name_of_first_project = 'Test program';
    $expected_description_of_first_project = 'My description';
    $expected_username_of_first_project = 'John Doe';
    $scratch_info_data = [$expected_id_of_first_project => [
      'id' => $expected_id_of_first_project,
      'creator' => ['username' => $expected_username_of_first_project],
      'title' => $expected_name_of_first_project,
      'description' => $expected_description_of_first_project,
    ]];

    $this->scratch_project_repository
      ->expects($this->atLeastOnce())
      ->method('find')->with($expected_id_of_first_project)
      ->willReturn(null)
    ;

    $this->entity_manager
      ->expects($this->atLeastOnce())
      ->method('persist')->with($this->isInstanceOf(ScratchProject::class))
      ->will($this->returnCallback(function (ScratchProject $scratch_project) use (
        $expected_id_of_first_project, $expected_name_of_first_project,
        $expected_description_of_first_project, $expected_username_of_first_project
      ) {
        $this->assertInstanceOf(ScratchProject::class, $scratch_project);
        $this->assertSame($expected_id_of_first_project, $scratch_project->getId());
        $this->assertSame($expected_name_of_first_project, $scratch_project->getName());
        $this->assertSame($expected_description_of_first_project, $scratch_project->getDescription());
        $this->assertSame($expected_username_of_first_project, $scratch_project->getUsername());
      }))
    ;
    $this->entity_manager->expects($this->atLeastOnce())->method('flush');
    $this->remix_manager->addScratchProjects($scratch_info_data);
  }

  /**
   * @throws \Exception
   */
  public function testAddSingleScratchProjectWithMissingData(): void
  {
    $expected_id_of_first_project = '123';
    $scratch_info_data = [$expected_id_of_first_project => []];

    $this->scratch_project_repository
      ->expects($this->atLeastOnce())
      ->method('find')->with($expected_id_of_first_project)
      ->willReturn(null)
    ;

    $this->entity_manager
      ->expects($this->atLeastOnce())
      ->method('persist')->with($this->isInstanceOf(ScratchProject::class))
      ->will($this->returnCallback(function (ScratchProject $scratch_project) use ($expected_id_of_first_project) {
        $this->assertInstanceOf(ScratchProject::class, $scratch_project);
        $this->assertSame($expected_id_of_first_project, $scratch_project->getId());
        $this->assertNull($scratch_project->getName());
        $this->assertNull($scratch_project->getDescription());
        $this->assertNull($scratch_project->getUsername());
      }))
    ;

    $this->entity_manager->expects($this->atLeastOnce())->method('flush');
    $this->remix_manager->addScratchProjects($scratch_info_data);
  }

  /**
   * @throws \Exception
   */
  public function testAddMultipleScratchProjects(): void
  {
    $expected_id_of_first_project = '123';
    $expected_name_of_first_project = 'Test program';
    $expected_description_of_first_project = 'My description';
    $expected_username_of_first_project = 'John Doe';
    $expected_id_of_second_project = '121';
    $expected_name_of_second_project = 'Other test program';
    $expected_username_of_second_project = 'Chuck Norris';
    $scratch_info_data = [
      $expected_id_of_first_project => [
        'id' => $expected_id_of_first_project,
        'creator' => ['username' => $expected_username_of_first_project],
        'title' => $expected_name_of_first_project,
        'description' => $expected_description_of_first_project,
      ], $expected_id_of_second_project => [
        'id' => $expected_id_of_second_project,
        'creator' => ['username' => $expected_username_of_second_project],
        'title' => $expected_name_of_second_project,
      ],
    ];

    $this->scratch_project_repository
      ->expects($this->exactly(2))
      ->method('find')
      ->willReturn(null)
    ;

    $this->entity_manager
      ->expects($this->atLeastOnce())
      ->method('persist')->with($this->isInstanceOf(ScratchProject::class))
      ->will($this->returnCallback(function (ScratchProject $scratch_project) use (
        $expected_id_of_first_project, $expected_name_of_first_project,
        $expected_description_of_first_project, $expected_username_of_first_project,
        $expected_id_of_second_project, $expected_name_of_second_project, $expected_username_of_second_project
      ) {
        $this->assertInstanceOf(ScratchProject::class, $scratch_project);
        if ($scratch_project->getId() === $expected_id_of_first_project) {
          $this->assertSame($expected_name_of_first_project, $scratch_project->getName());
          $this->assertSame($expected_description_of_first_project, $scratch_project->getDescription());
          $this->assertSame($expected_username_of_first_project, $scratch_project->getUsername());
        } elseif ($scratch_project->getId() === $expected_id_of_second_project) {
          $this->assertSame($expected_name_of_second_project, $scratch_project->getName());
          $this->assertNull($scratch_project->getDescription());
          $this->assertSame($expected_username_of_second_project, $scratch_project->getUsername());
        }
      }))
    ;

    $this->entity_manager->expects($this->atLeastOnce())->method('flush');
    $this->remix_manager->addScratchProjects($scratch_info_data);
  }

  /**
   * @throws \Exception
   */
  public function testSetProjectAsRootAndDontAddRemixRelationsWhenNoParentsAreGiven(): void
  {
    $project_entity = $this->createMock(Project::class);
    $project_entity->expects($this->atLeastOnce())->method('getId')->willReturn('123');
    $project_entity->expects($this->atLeastOnce())->method('isInitialVersion')->willReturn(true);

    $parent_data = [];
    $expected_relations = [
      new ProjectRemixRelation($project_entity, $project_entity, 0),
    ];

    $this->checkRemixRelations($project_entity, $parent_data, $expected_relations);
    $this->assertTrue($project_entity->isRemixRoot());
  }

  /**
   * @throws \Exception
   */
  public function testSetProjectAsRootAndDontAddRemixRelationsForNonExistingParents(): void
  {
    $project_entity = $this->createMock(Project::class);
    $project_entity->expects($this->atLeastOnce())->method('getId')->willReturn('123');
    $project_entity->expects($this->atLeastOnce())->method('isInitialVersion')->willReturn(true);

    $first_parent_entity = $this->createMock(Project::class);
    $first_parent_entity->expects($this->atLeastOnce())->method('getId')->willReturn('3570');
    $first_parent_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;

    $second_parent_entity = $this->createMock(Project::class);
    $second_parent_entity->expects($this->atLeastOnce())->method('getId')->willReturn('16267');
    $second_parent_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;

    $parent_data = [
      $first_parent_entity->getId() ?? 'first' => [
        'isScratch' => false,
        'entity' => $first_parent_entity,
        'exists' => false,
        'existingRelations' => [],
      ],
      $second_parent_entity->getId() ?? 'second' => [
        'isScratch' => false,
        'entity' => $second_parent_entity,
        'exists' => false,
        'existingRelations' => [],
      ],
    ];
    $expected_relations = [
      new ProjectRemixRelation($project_entity, $project_entity, 0),
    ];

    $this->checkRemixRelations($project_entity, $parent_data, $expected_relations);
    $this->assertTrue($project_entity->isRemixRoot());
  }

  /**
   * @throws \Exception
   */
  public function testSetProjectAsRootIfOnlyHasScratchParents(): void
  {
    // --------------------------------------------------------------------------------------------------------------
    //
    //    (Scratch #1)   (Scratch #2)
    //         \             /
    //          \           /
    //           \         /
    //              (123)                <--------- to be added
    //
    // --------------------------------------------------------------------------------------------------------------
    $project_entity = $this->createMock(Project::class);
    $project_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('123')
    ;
    $project_entity->expects($this->atLeastOnce())
      ->method('isInitialVersion')->willReturn(true)
    ;
    $project_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $first_scratch_parent_id = '1';
    $second_scratch_parent_id = '2';
    $parent_data = [
      $first_scratch_parent_id => [
        'isScratch' => true,
        'entity' => null,
        'exists' => true,
        'existingRelations' => [],
      ],
      $second_scratch_parent_id => [
        'isScratch' => true,
        'entity' => null,
        'exists' => true,
        'existingRelations' => [],
      ],
    ];
    $expected_relations = [
      new ProjectRemixRelation($project_entity, $project_entity, 0),
      new ScratchProjectRemixRelation($first_scratch_parent_id, $project_entity),
      new ScratchProjectRemixRelation($second_scratch_parent_id, $project_entity),
    ];

    $this->checkRemixRelations($project_entity, $parent_data, $expected_relations);
    $this->assertTrue($project_entity->isRemixRoot());
  }

  /**
   * @throws \Exception
   */
  public function testAddRemixRelationsForOnlyOneExistingParent(): void
  {
    // --------------------------------------------------------------------------------------------------------------
    //
    //    doesn't exist any more -->            (3570)    (16267)
    //                                                       |
    //                                                     (123)              <--------- to be added
    //
    // --------------------------------------------------------------------------------------------------------------

    $project_entities = $this->getProjectEntityAndParents('2');

    /** @var Project $project_entity */
    $project_entity = $project_entities[0];

    /** @var Project $first_parent_entity */
    $first_parent_entity = $project_entities[1];

    /** @var Project $second_parent_entity */
    $second_parent_entity = $project_entities[2];

    $parent_data = [
      $first_parent_entity->getId() ?? '' => [
        'isScratch' => false,
        'entity' => $first_parent_entity,
        'exists' => false,
        'existingRelations' => [],
      ],
      $second_parent_entity->getId() ?? '' => [
        'isScratch' => false,
        'entity' => $second_parent_entity,
        'exists' => true,
        'existingRelations' => [],
      ],
    ];
    $expected_relations = [
      new ProjectRemixRelation($project_entity, $project_entity, 0),
      new ProjectRemixRelation($second_parent_entity, $project_entity, 1),
    ];
    $this->checkRemixRelations($project_entity, $parent_data, $expected_relations);
    $this->assertFalse($project_entity->isRemixRoot());
  }

  /**
   * @throws \Exception
   */
  public function testAddRemixRelationsForExistingParents(): void
  {
    // --------------------------------------------------------------------------------------------------------------
    //
    //                 (3570)    (16267)
    //                     \       /
    //                       (123)              <--------- to be added
    //
    // --------------------------------------------------------------------------------------------------------------
    $project_entities = $this->getProjectEntityAndParents();

    $project_entity = $project_entities[0];

    $first_parent_entity = $project_entities[1];

    $second_parent_entity = $project_entities[2];

    $parent_data = [
      $first_parent_entity->getId() => [
        'isScratch' => false,
        'entity' => $first_parent_entity,
        'exists' => true,
        'existingRelations' => [],
      ],
      $second_parent_entity->getId() => [
        'isScratch' => false,
        'entity' => $second_parent_entity,
        'exists' => true,
        'existingRelations' => [],
      ],
    ];
    $expected_relations = [
      new ProjectRemixRelation($project_entity, $project_entity, 0),
      new ProjectRemixRelation($first_parent_entity, $project_entity, 1),
      new ProjectRemixRelation($second_parent_entity, $project_entity, 1),
    ];
    $this->checkRemixRelations($project_entity, $parent_data, $expected_relations);
    $this->assertFalse($project_entity->isRemixRoot());
  }

  /**
   * @throws \Exception
   */
  public function testAddRemixRelationsForExistingParentsSharingSameParent(): void
  {
    // --------------------------------------------------------------------------------------------------------------
    //                       (1)
    //                     /     \
    //                    (2)   (3)
    //                     \     /
    //                       (4)              <--------- to be added
    //
    // --------------------------------------------------------------------------------------------------------------

    $parent_entity_of_both_parents = $this->createMock(Project::class);
    $parent_entity_of_both_parents->expects($this->atLeastOnce())
      ->method('getId')->willReturn('1')
    ;
    $parent_entity_of_both_parents->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;

    $entities = $this->getProjectEntityAndParents('4', '2', '3');
    $project_entity = $entities[0];
    $first_parent_entity = $entities[1];
    $second_parent_entity = $entities[2];

    $parent_data = [
      $first_parent_entity->getId() => [
        'isScratch' => false,
        'entity' => $first_parent_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($first_parent_entity, $first_parent_entity, 0),
          new ProjectRemixRelation($parent_entity_of_both_parents, $first_parent_entity, 1),
        ],
      ],
      $second_parent_entity->getId() => [
        'isScratch' => false,
        'entity' => $second_parent_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($second_parent_entity, $second_parent_entity, 0),
          new ProjectRemixRelation($parent_entity_of_both_parents, $second_parent_entity, 1),
        ],
      ],
    ];

    $expected_relations = [
      // self-relation
      new ProjectRemixRelation($project_entity, $project_entity, 0),

      // relation to parents
      new ProjectRemixRelation($first_parent_entity, $project_entity, 1),
      new ProjectRemixRelation($second_parent_entity, $project_entity, 1),

      // relation to grandparents
      new ProjectRemixRelation($parent_entity_of_both_parents, $project_entity, 2),
    ];
    $this->checkRemixRelations($project_entity, $parent_data, $expected_relations);
    $this->assertFalse($project_entity->isRemixRoot());
  }

  /**
   * @throws \Exception
   */
  public function testAddRemixRelationsForExistingParentsHavingDifferentParent(): void
  {
    // --------------------------------------------------------------------------------------------------------------
    //                    (1)    (2)
    //                     |      |
    //                    (3)    (4)
    //                      \     /
    //                        (5)              <--------- to be added
    //
    // --------------------------------------------------------------------------------------------------------------
    $parent_entity_of_first_parent = $this->createMock(Project::class);
    $parent_entity_of_first_parent->expects($this->atLeastOnce())
      ->method('getId')->willReturn('1')
    ;
    $parent_entity_of_first_parent->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $parent_entity_of_second_parent = $this->createMock(Project::class);
    $parent_entity_of_second_parent->expects($this->atLeastOnce())
      ->method('getId')->willReturn('2')
    ;
    $parent_entity_of_second_parent->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $first_parent_entity = $this->createMock(Project::class);
    $first_parent_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('3')
    ;
    $first_parent_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $second_parent_entity = $this->createMock(Project::class);
    $second_parent_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('4')
    ;
    $second_parent_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $project_entity = $this->createMock(Project::class);
    $project_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('5')
    ;
    $project_entity->expects($this->atLeastOnce())
      ->method('isInitialVersion')->willReturn(true)
    ;
    $project_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $parent_data = [
      $first_parent_entity->getId() ?? 'first' => [
        'isScratch' => false,
        'entity' => $first_parent_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($first_parent_entity, $first_parent_entity, 0),
          new ProjectRemixRelation($parent_entity_of_first_parent, $first_parent_entity, 1),
        ],
      ],
      $second_parent_entity->getId() ?? 'second' => [
        'isScratch' => false,
        'entity' => $second_parent_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($second_parent_entity, $second_parent_entity, 0),
          new ProjectRemixRelation($parent_entity_of_second_parent, $second_parent_entity, 1),
        ],
      ],
    ];
    $expected_relations = [
      // self-relation
      new ProjectRemixRelation($project_entity, $project_entity, 0),

      // relation to parents
      new ProjectRemixRelation($first_parent_entity, $project_entity, 1),
      new ProjectRemixRelation($second_parent_entity, $project_entity, 1),

      // relation to grandparents
      new ProjectRemixRelation($parent_entity_of_first_parent, $project_entity, 2),
      new ProjectRemixRelation($parent_entity_of_second_parent, $project_entity, 2),
    ];
    $this->checkRemixRelations($project_entity, $parent_data, $expected_relations);
    $this->assertFalse($project_entity->isRemixRoot());
  }

  /**
   * @throws \Exception
   */
  public function testAddRemixRelationsForScratchParent(): void
  {
    // --------------------------------------------------------------------------------------------------------------
    //                    (1) (SCRATCH)
    //                     |      |   \
    //                    (2)    (3)  |
    //                      \     /   |
    //                        (4) ____/        <--------- to be added
    //
    // --------------------------------------------------------------------------------------------------------------
    $scratch_parent_id = '29495624';

    $parent_entity_of_first_parent = $this->createMock(Project::class);
    $parent_entity_of_first_parent->expects($this->atLeastOnce())
      ->method('getId')->willReturn('1')
    ;
    $parent_entity_of_first_parent->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;

    $entities = $this->getProjectEntityAndParents('4', '2', '3');
    $project_entity = $entities[0];
    $first_parent_entity = $entities[1];
    $second_parent_entity = $entities[2];

    $parent_data = [
      $first_parent_entity->getId() => [
        'isScratch' => false,
        'entity' => $first_parent_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($first_parent_entity, $first_parent_entity, 0),
          new ProjectRemixRelation($parent_entity_of_first_parent, $first_parent_entity, 1),
        ],
      ],
      $second_parent_entity->getId() => [
        'isScratch' => false,
        'entity' => $second_parent_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($second_parent_entity, $second_parent_entity, 0),
          new ScratchProjectRemixRelation($scratch_parent_id, $second_parent_entity),
        ],
      ],
      $scratch_parent_id => [
        'isScratch' => true,
        'entity' => null,
        'exists' => true,
        'existingRelations' => [],
      ],
    ];
    $expected_relations = [
      // self-relation
      new ProjectRemixRelation($project_entity, $project_entity, 0),

      // relation to parents
      new ProjectRemixRelation($first_parent_entity, $project_entity, 1),
      new ProjectRemixRelation($second_parent_entity, $project_entity, 1),
      new ScratchProjectRemixRelation($scratch_parent_id, $project_entity),

      // relation to grandparents
      new ProjectRemixRelation($parent_entity_of_first_parent, $project_entity, 2),
    ];
    $this->checkRemixRelations($project_entity, $parent_data, $expected_relations);
    $this->assertFalse($project_entity->isRemixRoot());
  }

  /**
   * @throws \Exception
   */
  public function testAddRemixRelationsForMoreComplexGraph1(): void
  {
    // --------------------------------------------------------------------------------------------------------------
    //
    //                (1)      (2)
    //                   \   /     \
    //                    \ /       \
    //                    (3)      (4)
    //                       \     /
    //                        \   /
    //                         (5)             <--------- to be added
    //
    // --------------------------------------------------------------------------------------------------------------

    $project_entities = $this->getProjectEntities(5);
    $first_project_entity = $project_entities[0];
    $second_project_entity = $project_entities[1];
    $third_project_entity = $project_entities[2];
    $fourth_project_entity = $project_entities[3];
    $project_entity = $project_entities[4];

    $project_entity->expects($this->atLeastOnce())
      ->method('isInitialVersion')->willReturn(true)
    ;
    $parent_data = [
      $third_project_entity->getId() => [
        'isScratch' => false,
        'entity' => $third_project_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($third_project_entity, $third_project_entity, 0),
          new ProjectRemixRelation($first_project_entity, $third_project_entity, 1),
          new ProjectRemixRelation($second_project_entity, $third_project_entity, 1),
        ],
      ],
      $fourth_project_entity->getId() => [
        'isScratch' => false,
        'entity' => $fourth_project_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($fourth_project_entity, $fourth_project_entity, 0),
          new ProjectRemixRelation($second_project_entity, $fourth_project_entity, 1),
        ],
      ],
    ];
    $expected_relations = [
      // self-relation
      new ProjectRemixRelation($project_entity, $project_entity, 0),

      // relation to parents
      new ProjectRemixRelation($third_project_entity, $project_entity, 1),
      new ProjectRemixRelation($fourth_project_entity, $project_entity, 1),

      // relation to grandparents
      new ProjectRemixRelation($first_project_entity, $project_entity, 2),
      new ProjectRemixRelation($second_project_entity, $project_entity, 2),
    ];
    $this->checkRemixRelations($project_entity, $parent_data, $expected_relations);
    $this->assertFalse($project_entity->isRemixRoot());
  }

  /**
   * @throws \Exception
   */
  public function testAddRemixRelationsForMoreComplexGraph2(): void
  {
    // --------------------------------------------------------------------------------------------------------------
    //
    //                (1)      (2)
    //                   \   /     \
    //                    \ /       \
    //                    (3)      (4)
    //                     |      / |
    //                     |     /  |
    //                     |    /   |
    //                     |   /    |
    //                     |  /     |
    //                    (5)      (6)
    //                      \     /
    //                        (7)              <--------- to be added
    //
    // --------------------------------------------------------------------------------------------------------------
    $project_entities = $this->getProjectEntities(7);
    $first_project_entity = $project_entities[0];
    $second_project_entity = $project_entities[1];
    $third_project_entity = $project_entities[2];
    $fourth_project_entity = $project_entities[3];
    $fifth_project_entity = $project_entities[4];
    $sixth_project_entity = $project_entities[5];

    $project_entity = $project_entities[6];

    $parent_data = [
      $fifth_project_entity->getId() => [
        'isScratch' => false,
        'entity' => $fifth_project_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($fifth_project_entity, $fifth_project_entity, 0),
          new ProjectRemixRelation($third_project_entity, $fifth_project_entity, 1),
          new ProjectRemixRelation($fourth_project_entity, $fifth_project_entity, 1),
          new ProjectRemixRelation($first_project_entity, $fifth_project_entity, 2),
          new ProjectRemixRelation($second_project_entity, $fifth_project_entity, 2),
        ],
      ],
      $sixth_project_entity->getId() => [
        'isScratch' => false,
        'entity' => $sixth_project_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($sixth_project_entity, $sixth_project_entity, 0),
          new ProjectRemixRelation($fourth_project_entity, $sixth_project_entity, 1),
          new ProjectRemixRelation($second_project_entity, $sixth_project_entity, 2),
        ],
      ],
    ];
    $expected_relations = [
      // self-relation
      new ProjectRemixRelation($project_entity, $project_entity, 0),

      // relation to parents
      new ProjectRemixRelation($fifth_project_entity, $project_entity, 1),
      new ProjectRemixRelation($sixth_project_entity, $project_entity, 1),

      // relation to grandparents
      new ProjectRemixRelation($third_project_entity, $project_entity, 2),
      new ProjectRemixRelation($fourth_project_entity, $project_entity, 2),

      // relation to parents of grandparents
      new ProjectRemixRelation($first_project_entity, $project_entity, 3),
      new ProjectRemixRelation($second_project_entity, $project_entity, 3),
    ];
    $this->checkRemixRelations($project_entity, $parent_data, $expected_relations);
    $this->assertFalse($project_entity->isRemixRoot());
  }

  /**
   * @throws \Exception
   */
  public function testAddRemixRelationsForMoreComplexGraph3(): void
  {
    // --------------------------------------------------------------------------------------------------------------
    //
    //                (1)      (2)
    //                  \    /  |  \
    //                   \  /   |   \
    //                   (3)    /  (4)
    //                     \   /    |
    //                      \ /     |
    //                      (5)    (6)
    //                        \    /
    //                          (7)              <--------- to be added
    //
    // --------------------------------------------------------------------------------------------------------------
    $project_entities = $this->getProjectEntities(7);
    $first_project_entity = $project_entities[0];
    $second_project_entity = $project_entities[1];
    $third_project_entity = $project_entities[2];
    $fourth_project_entity = $project_entities[3];
    $fifth_project_entity = $project_entities[4];
    $sixth_project_entity = $project_entities[5];

    $project_entity = $project_entities[6];

    $parent_data = [
      $fifth_project_entity->getId() => [
        'isScratch' => false,
        'entity' => $fifth_project_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($fifth_project_entity, $fifth_project_entity, 0),
          new ProjectRemixRelation($third_project_entity, $fifth_project_entity, 1),
          new ProjectRemixRelation($second_project_entity, $fifth_project_entity, 1),
          new ProjectRemixRelation($first_project_entity, $fifth_project_entity, 2),
          new ProjectRemixRelation($second_project_entity, $fifth_project_entity, 2),
        ],
      ],
      $sixth_project_entity->getId() => [
        'isScratch' => false,
        'entity' => $sixth_project_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($sixth_project_entity, $sixth_project_entity, 0),
          new ProjectRemixRelation($fourth_project_entity, $sixth_project_entity, 1),
          new ProjectRemixRelation($second_project_entity, $sixth_project_entity, 2),
        ],
      ],
    ];
    $expected_relations = [
      // self-relation
      new ProjectRemixRelation($project_entity, $project_entity, 0),

      // relation to parents
      new ProjectRemixRelation($fifth_project_entity, $project_entity, 1),
      new ProjectRemixRelation($sixth_project_entity, $project_entity, 1),

      // relation to grandparents
      new ProjectRemixRelation($third_project_entity, $project_entity, 2),
      new ProjectRemixRelation($second_project_entity, $project_entity, 2),
      new ProjectRemixRelation($fourth_project_entity, $project_entity, 2),

      // relation to parents of grandparents
      new ProjectRemixRelation($first_project_entity, $project_entity, 3),
      new ProjectRemixRelation($second_project_entity, $project_entity, 3),
    ];
    $this->checkRemixRelations($project_entity, $parent_data, $expected_relations);
    $this->assertFalse($project_entity->isRemixRoot());
  }

  /**
   * @throws \Exception
   */
  public function testAddRemixRelationsForMoreComplexGraph4(): void
  {
    // --------------------------------------------------------------------------------------------------------------
    //
    //                (1)      (2)--------
    //                  \    /  |  \       \
    //                   \  /   |   \      |
    //                   (3)    /  (4)     |
    //                  /  \   /__/ |      |
    //                 |    \ /     |      /
    //                 |    (5)    (6)----
    //                 |      \    /
    //                  \______ (7)              <--------- to be added
    //
    // --------------------------------------------------------------------------------------------------------------

    $project_entities = $this->getProjectEntities(7);
    $first_project_entity = $project_entities[0];
    $second_project_entity = $project_entities[1];
    $third_project_entity = $project_entities[2];
    $fourth_project_entity = $project_entities[3];
    $fifth_project_entity = $project_entities[4];
    $sixth_project_entity = $project_entities[5];

    $project_entity = $project_entities[6];

    $parent_data = [
      $third_project_entity->getId() => [
        'isScratch' => false,
        'entity' => $third_project_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($third_project_entity, $third_project_entity, 0),
          new ProjectRemixRelation($first_project_entity, $third_project_entity, 1),
          new ProjectRemixRelation($second_project_entity, $third_project_entity, 1),
        ],
      ],
      $fifth_project_entity->getId() => [
        'isScratch' => false,
        'entity' => $fifth_project_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($fifth_project_entity, $fifth_project_entity, 0),
          new ProjectRemixRelation($third_project_entity, $fifth_project_entity, 1),
          new ProjectRemixRelation($second_project_entity, $fifth_project_entity, 1),
          new ProjectRemixRelation($fourth_project_entity, $fifth_project_entity, 1),
          new ProjectRemixRelation($first_project_entity, $fifth_project_entity, 2),
          new ProjectRemixRelation($second_project_entity, $fifth_project_entity, 2),
        ],
      ],
      $sixth_project_entity->getId() => [
        'isScratch' => false,
        'entity' => $sixth_project_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($sixth_project_entity, $sixth_project_entity, 0),
          new ProjectRemixRelation($fourth_project_entity, $sixth_project_entity, 1),
          new ProjectRemixRelation($second_project_entity, $sixth_project_entity, 1),
          new ProjectRemixRelation($second_project_entity, $sixth_project_entity, 2),
        ],
      ],
    ];
    $expected_relations = [
      // self-relation
      new ProjectRemixRelation($project_entity, $project_entity, 0),

      // relation to parents
      new ProjectRemixRelation($third_project_entity, $project_entity, 1),
      new ProjectRemixRelation($fifth_project_entity, $project_entity, 1),
      new ProjectRemixRelation($sixth_project_entity, $project_entity, 1),

      // relation to grandparents
      new ProjectRemixRelation($first_project_entity, $project_entity, 2),
      new ProjectRemixRelation($second_project_entity, $project_entity, 2),
      new ProjectRemixRelation($third_project_entity, $project_entity, 2),
      new ProjectRemixRelation($fourth_project_entity, $project_entity, 2),

      // relation to parents of grandparents
      new ProjectRemixRelation($first_project_entity, $project_entity, 3),
      new ProjectRemixRelation($second_project_entity, $project_entity, 3),
    ];
    $this->checkRemixRelations($project_entity, $parent_data, $expected_relations);
    $this->assertFalse($project_entity->isRemixRoot());
  }

  /**
   * @throws \Exception
   */
  public function testAddRemixRelationsForMoreComplexGraph5(): void
  {
    // --------------------------------------------------------------------------------------------------------------
    //
    //                (1)    (SCRATCH) ___
    //                  \    /  |  \      \
    //                   \  /   |   \      |
    //                   (2)    /  (3)     |
    //                  /  \   /__/ |      |
    //                 |    \ /     |      |
    //                 |    (4)    (5)____/|
    //                 |      \    /       |
    //                  \______ (6) _______/     <--------- to be added
    //
    // --------------------------------------------------------------------------------------------------------------

    $scratch_parent_id = '29495624';

    $project_entities = $this->getProjectEntities(6);
    $first_project_entity = $project_entities[0];
    $second_project_entity = $project_entities[1];
    $third_project_entity = $project_entities[2];
    $fourth_project_entity = $project_entities[3];
    $fifth_project_entity = $project_entities[4];

    $project_entity = $project_entities[5];

    $parent_data = [
      $second_project_entity->getId() => [
        'isScratch' => false,
        'entity' => $second_project_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($second_project_entity, $second_project_entity, 0),
          new ProjectRemixRelation($first_project_entity, $second_project_entity, 1),
          new ScratchProjectRemixRelation($scratch_parent_id, $second_project_entity),
        ],
      ],
      $fourth_project_entity->getId() => [
        'isScratch' => false,
        'entity' => $fourth_project_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($fourth_project_entity, $fourth_project_entity, 0),
          new ProjectRemixRelation($third_project_entity, $fourth_project_entity, 1),
          new ProjectRemixRelation($second_project_entity, $fourth_project_entity, 1),
          new ScratchProjectRemixRelation($scratch_parent_id, $fourth_project_entity),
          new ProjectRemixRelation($first_project_entity, $fourth_project_entity, 2),
        ],
      ],
      $fifth_project_entity->getId() => [
        'isScratch' => false,
        'entity' => $fifth_project_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($fifth_project_entity, $fifth_project_entity, 0),
          new ProjectRemixRelation($third_project_entity, $fifth_project_entity, 1),
          new ScratchProjectRemixRelation($scratch_parent_id, $fifth_project_entity),
        ],
      ],
      $scratch_parent_id => [
        'isScratch' => true,
        'entity' => null,
        'exists' => true,
        'existingRelations' => [],
      ],
    ];
    $expected_relations = [
      // self-relation
      new ProjectRemixRelation($project_entity, $project_entity, 0),

      // relation to parents
      new ProjectRemixRelation($second_project_entity, $project_entity, 1),
      new ProjectRemixRelation($fourth_project_entity, $project_entity, 1),
      new ProjectRemixRelation($fifth_project_entity, $project_entity, 1),
      new ScratchProjectRemixRelation($scratch_parent_id, $project_entity),

      // relation to grandparents
      new ProjectRemixRelation($first_project_entity, $project_entity, 2),
      new ProjectRemixRelation($second_project_entity, $project_entity, 2),
      new ProjectRemixRelation($third_project_entity, $project_entity, 2),

      // relation to parents of grandparents
      new ProjectRemixRelation($first_project_entity, $project_entity, 3),
    ];
    $this->checkRemixRelations($project_entity, $parent_data, $expected_relations);
    $this->assertFalse($project_entity->isRemixRoot());
  }

  /**
   * @throws \Exception
   */
  public function testAddRemixRelationsForMoreComplexGraph6(): void
  {
    // --------------------------------------------------------------------------------------------------------------
    //
    //      (SCRATCH #1)   (SCRATCH #2) __
    //                  \    /  |  \      \
    //                   \  /   |   \      |
    //                   (1)    /  (2)     |
    //                  /  \   /__/ |      |
    //                 |    \ /     |      |
    //                 |    (3)    (4)____/|
    //                 |      \    /       |
    //                  \______ (5) _______/     <--------- to be added
    //
    // --------------------------------------------------------------------------------------------------------------
    $first_scratch_ancestor_id = '124742637';
    $second_scratch_parent_id = '29495624';

    $project_entities = $this->getProjectEntities(5);
    $first_project_entity = $project_entities[0];
    $second_project_entity = $project_entities[1];
    $third_project_entity = $project_entities[2];
    $fourth_project_entity = $project_entities[3];

    $project_entity = $project_entities[4];

    $parent_data = [
      $first_project_entity->getId() => [
        'isScratch' => false,
        'entity' => $first_project_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($first_project_entity, $first_project_entity, 0),
          new ScratchProjectRemixRelation($first_scratch_ancestor_id, $first_project_entity),
          new ScratchProjectRemixRelation($second_scratch_parent_id, $first_project_entity),
        ],
      ],
      $third_project_entity->getId() => [
        'isScratch' => false,
        'entity' => $third_project_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($third_project_entity, $third_project_entity, 0),
          new ProjectRemixRelation($second_project_entity, $third_project_entity, 1),
          new ProjectRemixRelation($first_project_entity, $third_project_entity, 1),
          new ScratchProjectRemixRelation($second_scratch_parent_id, $third_project_entity),
        ],
      ],
      $fourth_project_entity->getId() => [
        'isScratch' => false,
        'entity' => $fourth_project_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($fourth_project_entity, $fourth_project_entity, 0),
          new ProjectRemixRelation($second_project_entity, $fourth_project_entity, 1),
          new ScratchProjectRemixRelation($second_scratch_parent_id, $fourth_project_entity),
        ],
      ],
      $second_scratch_parent_id => [
        'isScratch' => true,
        'entity' => null,
        'exists' => true,
        'existingRelations' => [],
      ],
    ];

    $expected_relations = [
      // self-relation
      new ProjectRemixRelation($project_entity, $project_entity, 0),

      // relation to parents
      new ProjectRemixRelation($first_project_entity, $project_entity, 1),
      new ProjectRemixRelation($third_project_entity, $project_entity, 1),
      new ProjectRemixRelation($fourth_project_entity, $project_entity, 1),
      new ScratchProjectRemixRelation($second_scratch_parent_id, $project_entity),

      // relation to grandparents
      new ProjectRemixRelation($first_project_entity, $project_entity, 2),
      new ProjectRemixRelation($second_project_entity, $project_entity, 2),
    ];
    $this->checkRemixRelations($project_entity, $parent_data, $expected_relations);
    $this->assertFalse($project_entity->isRemixRoot());
  }

  /**
   * @throws \Exception
   */
  public function testAddRemixRelationsForMoreComplexGraph7(): void
  {
    // --------------------------------------------------------------------------------------------------------------
    //
    //      (SCRATCH #1)   (SCRATCH #2) __
    //                  \    /  |  \      \
    //                   \  /   |   \      |
    //                   (1)    /  (2)     |
    //   (SCRATCH #3)   /  \   /__/ |      |
    //               \ |    \ /     |      |
    //                \|    (3)    (4)____/|
    //                 |      \    /       |
    //                  \______ (5) _______/     <--------- to be added
    //
    // --------------------------------------------------------------------------------------------------------------

    $first_scratch_ancestor_id = '127781769';
    $second_scratch_parent_id = '29495624';
    $third_scratch_parent_id = '124742637';

    $project_entities = $this->getProjectEntities(5);
    $first_project_entity = $project_entities[0];
    $second_project_entity = $project_entities[1];
    $third_project_entity = $project_entities[2];
    $fourth_project_entity = $project_entities[3];
    $project_entity = $project_entities[4];

    $existingRelationsFirstProjectEntity = [
      new ProjectRemixRelation($first_project_entity, $first_project_entity, 0),
      new ScratchProjectRemixRelation($first_scratch_ancestor_id, $first_project_entity),
      new ScratchProjectRemixRelation($second_scratch_parent_id, $first_project_entity),
    ];

    $existingRelationsThirdProjectEntity = [
      new ProjectRemixRelation($third_project_entity, $third_project_entity, 0),
      new ProjectRemixRelation($second_project_entity, $third_project_entity, 1),
      new ProjectRemixRelation($first_project_entity, $third_project_entity, 1),
      new ScratchProjectRemixRelation($second_scratch_parent_id, $third_project_entity),
    ];

    $existingRelationsFourthProjectEntity = [
      new ProjectRemixRelation($fourth_project_entity, $fourth_project_entity, 0),
      new ProjectRemixRelation($second_project_entity, $fourth_project_entity, 1),
      new ScratchProjectRemixRelation($second_scratch_parent_id, $fourth_project_entity),
    ];

    $parent_data = [
      $first_project_entity->getId() ?? 'first' => [
        'isScratch' => false,
        'entity' => $first_project_entity,
        'exists' => true,
        'existingRelations' => $existingRelationsFirstProjectEntity,
      ],
      $third_project_entity->getId() ?? 'third' => [
        'isScratch' => false,
        'entity' => $third_project_entity,
        'exists' => true,
        'existingRelations' => $existingRelationsThirdProjectEntity,
      ],
      $fourth_project_entity->getId() ?? 'fourth' => [
        'isScratch' => false,
        'entity' => $fourth_project_entity,
        'exists' => true,
        'existingRelations' => $existingRelationsFourthProjectEntity,
      ],
      $second_scratch_parent_id => [
        'isScratch' => true,
        'entity' => null,
        'exists' => true,
        'existingRelations' => [],
      ],
      $third_scratch_parent_id => [
        'isScratch' => true,
        'entity' => null,
        'exists' => true,
        'existingRelations' => [],
      ],
    ];

    $expected_relations = [
      // self-relation
      new ProjectRemixRelation($project_entity, $project_entity, 0),

      // relation to parents
      new ProjectRemixRelation($first_project_entity, $project_entity, 1),
      new ProjectRemixRelation($third_project_entity, $project_entity, 1),
      new ProjectRemixRelation($fourth_project_entity, $project_entity, 1),
      new ScratchProjectRemixRelation($second_scratch_parent_id, $project_entity),
      new ScratchProjectRemixRelation($third_scratch_parent_id, $project_entity),

      // relation to grandparents
      new ProjectRemixRelation($first_project_entity, $project_entity, 2),
      new ProjectRemixRelation($second_project_entity, $project_entity, 2),
    ];
    $this->checkRemixRelations($project_entity, $parent_data, $expected_relations);
    $this->assertFalse($project_entity->isRemixRoot());
  }

  /**
   * @param Project|MockObject $project_entity
   *
   * @throws \Exception
   */
  private function checkRemixRelations($project_entity, array $parent_data, array $expected_relations): void
  {
    /** @var MockObject|Project $project_entity */
    $expected_relations_map = [];
    $expected_catrobat_relations = [];
    foreach ($expected_relations as $expected_relation) {
      if ($expected_relation instanceof ProjectRemixRelation) {
        $expected_catrobat_relations[] = $expected_relation;
      }
      $expected_relations_map[$expected_relation->getUniqueKey()] = $expected_relation;
    }

    $project_repository_find_map = [];
    $project_remix_repository_find_map = [];

    foreach ($parent_data as $parent_id => $data) {
      $catrobat_relations = array_filter($data['existingRelations'], fn ($relation) => $relation instanceof ProjectRemixRelation);
      $project_remix_repository_find_map[] = [['descendant_id' => (string) $parent_id], null, null, null, $catrobat_relations];
      $project_repository_find_map[] = [(string) $parent_id, null, null, $data['exists'] ? $data['entity'] : null];
    }

    $this->project_repository
      ->expects($this->any())
      ->method('find')
      ->willReturn($this->returnValueMap($project_repository_find_map))
    ;

    $this->project_remix_repository
      ->expects($this->any())
      ->method('findBy')
      ->willReturn($this->returnValueMap($project_remix_repository_find_map))
    ;

    $this->entity_manager
      ->expects($this->atLeastOnce())
      ->method('persist')
      ->will($this->returnCallback(function ($arg) use ($project_entity, &$expected_relations_map) {
        if ($arg instanceof ProjectRemixRelation || $arg instanceof ScratchProjectRemixRelation) {
          $relation = $arg;
          Assert::assertArrayHasKey($relation->getUniqueKey(), $expected_relations_map);
          unset($expected_relations_map[$relation->getUniqueKey()]);
        }

        if ($arg instanceof Project) {
          Assert::assertEquals($arg, $project_entity);
        }
      }))
    ;

    $this->entity_manager->expects($this->atLeastOnce())->method('flush');

    $remixes_data = [];
    foreach ($parent_data as $parent_id => $data) {
      $remixes_data[] = new RemixData(!$data['isScratch'] ? '/app/project/'.$parent_id
        : 'https://scratch.mit.edu/projects/'.$parent_id.'/');
    }

    Assert::assertCount(count($expected_relations), $expected_relations_map);

    $expected_to_be_root = (1 === count($expected_catrobat_relations));
    $project_entity->expects($this->atLeastOnce())
      ->method('isRemixRoot')->willReturn($expected_to_be_root)
    ;
    $this->remix_manager->addRemixes($project_entity, $remixes_data);
    Assert::assertCount(0, $expected_relations_map);
  }

  private function getProjectEntities(int $amount): array
  {
    $array = [];
    for ($i = 1; $i <= $amount; ++$i) {
      $project_entity = $this->createMock(Project::class);
      $project_entity->expects($this->atLeastOnce())
        ->method('getId')
        ->willReturn(strval($i))
      ;
      $project_entity->expects($this->any())
        ->method('getUser')
        ->willReturn($this->createMock(User::class))
      ;
      if ($i === $amount) {
        $project_entity->expects($this->atLeastOnce())
          ->method('isInitialVersion')
          ->willReturn(true)
        ;
      }
      $array[] = $project_entity;
    }

    return $array;
  }

  private function getProjectEntityAndParents(string $entityReturn = '123', string $firstParentReturn = '3570', string $secParentReturn = '16267'): array
  {
    $project_entity = $this->createMock(Project::class);
    $project_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn($entityReturn)
    ;
    $project_entity->expects($this->atLeastOnce())
      ->method('isInitialVersion')->willReturn(true)
    ;
    $project_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;

    $first_parent_entity = $this->createMock(Project::class);
    $first_parent_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn($firstParentReturn)
    ;
    $first_parent_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;

    $second_parent_entity = $this->createMock(Project::class);
    $second_parent_entity->expects($this->atLeastOnce())
      ->method('getId')
      ->willReturn($secParentReturn)
    ;
    $second_parent_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;

    $array = [];
    array_push($array, $project_entity, $first_parent_entity, $second_parent_entity);

    return $array;
  }
}
