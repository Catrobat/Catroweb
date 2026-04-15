<?php

declare(strict_types=1);

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
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(RemixManager::class)]
#[AllowMockObjectsWithoutExpectations]
class RemixManagerTest extends TestCase
{
  private RemixManager $remix_manager;

  private MockObject $entity_manager;

  private MockObject $program_repository;

  private MockObject $scratch_program_repository;

  private MockObject $program_remix_repository;

  /**
   * @throws Exception
   */
  #[\Override]
  protected function setUp(): void
  {
    $this->entity_manager = $this->createMock(EntityManager::class);
    $this->program_repository = $this->createMock(ProjectRepository::class);
    $this->scratch_program_repository = $this->createMock(ScratchProjectRepository::class);
    $this->program_remix_repository = $this->createMock(ProjectRemixRepository::class);
    $program_remix_backward_repository = $this->createMock(ProjectRemixBackwardRepository::class);
    $scratch_program_remix_repository = $this->createMock(ScratchProjectRemixRepository::class);
    $remix_graph_manipulator = $this->createMock(RemixGraphManipulator::class);
    $catro_notification_service = $this->createMock(NotificationManager::class);
    $this->remix_manager = new RemixManager($this->entity_manager, $this->program_repository, $this->scratch_program_repository, $this->program_remix_repository, $program_remix_backward_repository, $scratch_program_remix_repository, $remix_graph_manipulator, $catro_notification_service);
  }

  /**
   * @throws \Exception
   */
  public function testAddSingleScratchProgram(): void
  {
    $expected_id_of_first_program = '123';
    $expected_name_of_first_program = 'Test program';
    $expected_description_of_first_program = 'My description';
    $expected_username_of_first_program = 'John Doe';
    $scratch_info_data = [$expected_id_of_first_program => [
      'id' => $expected_id_of_first_program,
      'creator' => ['username' => $expected_username_of_first_program],
      'title' => $expected_name_of_first_program,
      'description' => $expected_description_of_first_program,
    ]];

    $this->scratch_program_repository
      ->expects($this->atLeastOnce())
      ->method('find')->with($expected_id_of_first_program)
      ->willReturn(null)
    ;

    $this->entity_manager
      ->expects($this->atLeastOnce())
      ->method('persist')->with($this->isInstanceOf(ScratchProject::class))
      ->willReturnCallback(function (ScratchProject $scratch_project) use (
        $expected_id_of_first_program, $expected_name_of_first_program,
        $expected_description_of_first_program, $expected_username_of_first_program
      ): void {
        $this->assertSame($expected_id_of_first_program, $scratch_project->getId());
        $this->assertSame($expected_name_of_first_program, $scratch_project->getName());
        $this->assertSame($expected_description_of_first_program, $scratch_project->getDescription());
        $this->assertSame($expected_username_of_first_program, $scratch_project->getUsername());
      })
    ;
    $this->entity_manager->expects($this->atLeastOnce())->method('flush');
    $this->remix_manager->addScratchProjects($scratch_info_data);
  }

  /**
   * @throws \Exception
   */
  public function testAddSingleScratchProgramWithMissingData(): void
  {
    $expected_id_of_first_program = '123';
    $scratch_info_data = [$expected_id_of_first_program => []];

    $this->scratch_program_repository
      ->expects($this->atLeastOnce())
      ->method('find')->with($expected_id_of_first_program)
      ->willReturn(null)
    ;

    $this->entity_manager
      ->expects($this->atLeastOnce())
      ->method('persist')->with($this->isInstanceOf(ScratchProject::class))
      ->willReturnCallback(function (ScratchProject $scratch_project) use ($expected_id_of_first_program): void {
        $this->assertSame($expected_id_of_first_program, $scratch_project->getId());
        $this->assertNull($scratch_project->getName());
        $this->assertNull($scratch_project->getDescription());
        $this->assertNull($scratch_project->getUsername());
      })
    ;

    $this->entity_manager->expects($this->atLeastOnce())->method('flush');
    $this->remix_manager->addScratchProjects($scratch_info_data);
  }

  /**
   * @throws \Exception
   */
  public function testAddMultipleScratchPrograms(): void
  {
    $expected_id_of_first_program = '123';
    $expected_name_of_first_program = 'Test program';
    $expected_description_of_first_program = 'My description';
    $expected_username_of_first_program = 'John Doe';
    $expected_id_of_second_program = '121';
    $expected_name_of_second_program = 'Other test program';
    $expected_username_of_second_program = 'Chuck Norris';
    $scratch_info_data = [
      $expected_id_of_first_program => [
        'id' => $expected_id_of_first_program,
        'creator' => ['username' => $expected_username_of_first_program],
        'title' => $expected_name_of_first_program,
        'description' => $expected_description_of_first_program,
      ], $expected_id_of_second_program => [
        'id' => $expected_id_of_second_program,
        'creator' => ['username' => $expected_username_of_second_program],
        'title' => $expected_name_of_second_program,
      ],
    ];

    $this->scratch_program_repository
      ->expects($this->exactly(2))
      ->method('find')
      ->willReturn(null)
    ;

    $this->entity_manager
      ->expects($this->atLeastOnce())
      ->method('persist')->with($this->isInstanceOf(ScratchProject::class))
      ->willReturnCallback(function (ScratchProject $scratch_project) use (
        $expected_id_of_first_program, $expected_name_of_first_program,
        $expected_description_of_first_program, $expected_username_of_first_program,
        $expected_id_of_second_program, $expected_name_of_second_program, $expected_username_of_second_program
      ): void {
        if ($scratch_project->getId() === $expected_id_of_first_program) {
          $this->assertSame($expected_name_of_first_program, $scratch_project->getName());
          $this->assertSame($expected_description_of_first_program, $scratch_project->getDescription());
          $this->assertSame($expected_username_of_first_program, $scratch_project->getUsername());
        } elseif ($scratch_project->getId() === $expected_id_of_second_program) {
          $this->assertSame($expected_name_of_second_program, $scratch_project->getName());
          $this->assertNull($scratch_project->getDescription());
          $this->assertSame($expected_username_of_second_program, $scratch_project->getUsername());
        }
      })
    ;

    $this->entity_manager->expects($this->atLeastOnce())->method('flush');
    $this->remix_manager->addScratchProjects($scratch_info_data);
  }

  /**
   * @throws \Exception
   * @throws Exception
   */
  public function testSetProgramAsRootAndDontAddRemixRelationsWhenNoParentsAreGiven(): void
  {
    $program_entity = $this->createMock(Project::class);
    $program_entity->expects($this->atLeastOnce())->method('getId')->willReturn('123');
    $program_entity->expects($this->atLeastOnce())->method('isInitialVersion')->willReturn(true);

    $parent_data = [];
    $expected_relations = [
      new ProjectRemixRelation($program_entity, $program_entity, 0),
    ];

    $this->checkRemixRelations($program_entity, $parent_data, $expected_relations);
    $this->assertTrue($program_entity->isRemixRoot());
  }

  /**
   * @throws \Exception
   * @throws Exception
   */
  public function testSetProgramAsRootAndDontAddRemixRelationsForNonExistingParents(): void
  {
    $program_entity = $this->createMock(Project::class);
    $program_entity->expects($this->atLeastOnce())->method('getId')->willReturn('123');
    $program_entity->expects($this->atLeastOnce())->method('isInitialVersion')->willReturn(true);

    $first_parent_entity = $this->createMock(Project::class);
    $first_parent_entity->expects($this->atLeastOnce())->method('getId')->willReturn('3570');
    $first_parent_entity->method('getUser')->willReturn($this->createMock(User::class));

    $second_parent_entity = $this->createMock(Project::class);
    $second_parent_entity->expects($this->atLeastOnce())->method('getId')->willReturn('16267');
    $second_parent_entity->method('getUser')->willReturn($this->createMock(User::class));

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
      new ProjectRemixRelation($program_entity, $program_entity, 0),
    ];

    $this->checkRemixRelations($program_entity, $parent_data, $expected_relations);
    $this->assertTrue($program_entity->isRemixRoot());
  }

  /**
   * @throws \Exception
   * @throws Exception
   */
  public function testSetProgramAsRootIfOnlyHasScratchParents(): void
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
    $program_entity = $this->createMock(Project::class);
    $program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('123')
    ;
    $program_entity->expects($this->atLeastOnce())
      ->method('isInitialVersion')->willReturn(true)
    ;
    $program_entity->method('getUser')->willReturn($this->createMock(User::class));
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
      new ProjectRemixRelation($program_entity, $program_entity, 0),
      new ScratchProjectRemixRelation($first_scratch_parent_id, $program_entity),
      new ScratchProjectRemixRelation($second_scratch_parent_id, $program_entity),
    ];

    $this->checkRemixRelations($program_entity, $parent_data, $expected_relations);
    $this->assertTrue($program_entity->isRemixRoot());
  }

  /**
   * @throws \Exception
   * @throws Exception
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

    $program_entities = $this->getProgramEntityAndParents('2');

    /** @var Project $program_entity */
    $program_entity = $program_entities[0];

    /** @var Project $first_parent_entity */
    $first_parent_entity = $program_entities[1];

    /** @var Project $second_parent_entity */
    $second_parent_entity = $program_entities[2];

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
      new ProjectRemixRelation($program_entity, $program_entity, 0),
      new ProjectRemixRelation($second_parent_entity, $program_entity, 1),
    ];
    $this->checkRemixRelations($program_entity, $parent_data, $expected_relations);
    $this->assertFalse($program_entity->isRemixRoot());
  }

  /**
   * @throws \Exception
   * @throws Exception
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
    $program_entities = $this->getProgramEntityAndParents();

    $program_entity = $program_entities[0];

    $first_parent_entity = $program_entities[1];

    $second_parent_entity = $program_entities[2];

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
      new ProjectRemixRelation($program_entity, $program_entity, 0),
      new ProjectRemixRelation($first_parent_entity, $program_entity, 1),
      new ProjectRemixRelation($second_parent_entity, $program_entity, 1),
    ];
    $this->checkRemixRelations($program_entity, $parent_data, $expected_relations);
    $this->assertFalse($program_entity->isRemixRoot());
  }

  /**
   * @throws \Exception
   * @throws Exception
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
    $parent_entity_of_both_parents->method('getUser')->willReturn($this->createMock(User::class));

    $entities = $this->getProgramEntityAndParents('4', '2', '3');
    $program_entity = $entities[0];
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
      new ProjectRemixRelation($program_entity, $program_entity, 0),

      // relation to parents
      new ProjectRemixRelation($first_parent_entity, $program_entity, 1),
      new ProjectRemixRelation($second_parent_entity, $program_entity, 1),

      // relation to grandparents
      new ProjectRemixRelation($parent_entity_of_both_parents, $program_entity, 2),
    ];
    $this->checkRemixRelations($program_entity, $parent_data, $expected_relations);
    $this->assertFalse($program_entity->isRemixRoot());
  }

  /**
   * @throws \Exception
   * @throws Exception
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
    $parent_entity_of_first_parent->method('getUser')->willReturn($this->createMock(User::class));
    $parent_entity_of_second_parent = $this->createMock(Project::class);
    $parent_entity_of_second_parent->expects($this->atLeastOnce())
      ->method('getId')->willReturn('2')
    ;
    $parent_entity_of_second_parent->method('getUser')->willReturn($this->createMock(User::class));
    $first_parent_entity = $this->createMock(Project::class);
    $first_parent_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('3')
    ;
    $first_parent_entity->method('getUser')->willReturn($this->createMock(User::class));
    $second_parent_entity = $this->createMock(Project::class);
    $second_parent_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('4')
    ;
    $second_parent_entity->method('getUser')->willReturn($this->createMock(User::class));
    $program_entity = $this->createMock(Project::class);
    $program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('5')
    ;
    $program_entity->expects($this->atLeastOnce())
      ->method('isInitialVersion')->willReturn(true)
    ;
    $program_entity->method('getUser')->willReturn($this->createMock(User::class));
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
      new ProjectRemixRelation($program_entity, $program_entity, 0),

      // relation to parents
      new ProjectRemixRelation($first_parent_entity, $program_entity, 1),
      new ProjectRemixRelation($second_parent_entity, $program_entity, 1),

      // relation to grandparents
      new ProjectRemixRelation($parent_entity_of_first_parent, $program_entity, 2),
      new ProjectRemixRelation($parent_entity_of_second_parent, $program_entity, 2),
    ];
    $this->checkRemixRelations($program_entity, $parent_data, $expected_relations);
    $this->assertFalse($program_entity->isRemixRoot());
  }

  /**
   * @throws \Exception
   * @throws Exception
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
    $parent_entity_of_first_parent->method('getUser')->willReturn($this->createMock(User::class));

    $entities = $this->getProgramEntityAndParents('4', '2', '3');
    $program_entity = $entities[0];
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
      new ProjectRemixRelation($program_entity, $program_entity, 0),

      // relation to parents
      new ProjectRemixRelation($first_parent_entity, $program_entity, 1),
      new ProjectRemixRelation($second_parent_entity, $program_entity, 1),
      new ScratchProjectRemixRelation($scratch_parent_id, $program_entity),

      // relation to grandparents
      new ProjectRemixRelation($parent_entity_of_first_parent, $program_entity, 2),
    ];
    $this->checkRemixRelations($program_entity, $parent_data, $expected_relations);
    $this->assertFalse($program_entity->isRemixRoot());
  }

  /**
   * @throws \Exception
   * @throws Exception
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

    $program_entities = $this->getProgramEntities(5);
    $first_program_entity = $program_entities[0];
    $second_program_entity = $program_entities[1];
    $third_program_entity = $program_entities[2];
    $fourth_program_entity = $program_entities[3];
    $program_entity = $program_entities[4];

    $program_entity->expects($this->atLeastOnce())
      ->method('isInitialVersion')->willReturn(true)
    ;
    $parent_data = [
      $third_program_entity->getId() => [
        'isScratch' => false,
        'entity' => $third_program_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($third_program_entity, $third_program_entity, 0),
          new ProjectRemixRelation($first_program_entity, $third_program_entity, 1),
          new ProjectRemixRelation($second_program_entity, $third_program_entity, 1),
        ],
      ],
      $fourth_program_entity->getId() => [
        'isScratch' => false,
        'entity' => $fourth_program_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($fourth_program_entity, $fourth_program_entity, 0),
          new ProjectRemixRelation($second_program_entity, $fourth_program_entity, 1),
        ],
      ],
    ];
    $expected_relations = [
      // self-relation
      new ProjectRemixRelation($program_entity, $program_entity, 0),

      // relation to parents
      new ProjectRemixRelation($third_program_entity, $program_entity, 1),
      new ProjectRemixRelation($fourth_program_entity, $program_entity, 1),

      // relation to grandparents
      new ProjectRemixRelation($first_program_entity, $program_entity, 2),
      new ProjectRemixRelation($second_program_entity, $program_entity, 2),
    ];
    $this->checkRemixRelations($program_entity, $parent_data, $expected_relations);
    $this->assertFalse($program_entity->isRemixRoot());
  }

  /**
   * @throws \Exception
   * @throws Exception
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
    $program_entities = $this->getProgramEntities(7);
    $first_program_entity = $program_entities[0];
    $second_program_entity = $program_entities[1];
    $third_program_entity = $program_entities[2];
    $fourth_program_entity = $program_entities[3];
    $fifth_program_entity = $program_entities[4];
    $sixth_program_entity = $program_entities[5];

    $program_entity = $program_entities[6];

    $parent_data = [
      $fifth_program_entity->getId() => [
        'isScratch' => false,
        'entity' => $fifth_program_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($fifth_program_entity, $fifth_program_entity, 0),
          new ProjectRemixRelation($third_program_entity, $fifth_program_entity, 1),
          new ProjectRemixRelation($fourth_program_entity, $fifth_program_entity, 1),
          new ProjectRemixRelation($first_program_entity, $fifth_program_entity, 2),
          new ProjectRemixRelation($second_program_entity, $fifth_program_entity, 2),
        ],
      ],
      $sixth_program_entity->getId() => [
        'isScratch' => false,
        'entity' => $sixth_program_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($sixth_program_entity, $sixth_program_entity, 0),
          new ProjectRemixRelation($fourth_program_entity, $sixth_program_entity, 1),
          new ProjectRemixRelation($second_program_entity, $sixth_program_entity, 2),
        ],
      ],
    ];
    $expected_relations = [
      // self-relation
      new ProjectRemixRelation($program_entity, $program_entity, 0),

      // relation to parents
      new ProjectRemixRelation($fifth_program_entity, $program_entity, 1),
      new ProjectRemixRelation($sixth_program_entity, $program_entity, 1),

      // relation to grandparents
      new ProjectRemixRelation($third_program_entity, $program_entity, 2),
      new ProjectRemixRelation($fourth_program_entity, $program_entity, 2),

      // relation to parents of grandparents
      new ProjectRemixRelation($first_program_entity, $program_entity, 3),
      new ProjectRemixRelation($second_program_entity, $program_entity, 3),
    ];
    $this->checkRemixRelations($program_entity, $parent_data, $expected_relations);
    $this->assertFalse($program_entity->isRemixRoot());
  }

  /**
   * @throws \Exception
   * @throws Exception
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
    $program_entities = $this->getProgramEntities(7);
    $first_program_entity = $program_entities[0];
    $second_program_entity = $program_entities[1];
    $third_program_entity = $program_entities[2];
    $fourth_program_entity = $program_entities[3];
    $fifth_program_entity = $program_entities[4];
    $sixth_program_entity = $program_entities[5];

    $program_entity = $program_entities[6];

    $parent_data = [
      $fifth_program_entity->getId() => [
        'isScratch' => false,
        'entity' => $fifth_program_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($fifth_program_entity, $fifth_program_entity, 0),
          new ProjectRemixRelation($third_program_entity, $fifth_program_entity, 1),
          new ProjectRemixRelation($second_program_entity, $fifth_program_entity, 1),
          new ProjectRemixRelation($first_program_entity, $fifth_program_entity, 2),
          new ProjectRemixRelation($second_program_entity, $fifth_program_entity, 2),
        ],
      ],
      $sixth_program_entity->getId() => [
        'isScratch' => false,
        'entity' => $sixth_program_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($sixth_program_entity, $sixth_program_entity, 0),
          new ProjectRemixRelation($fourth_program_entity, $sixth_program_entity, 1),
          new ProjectRemixRelation($second_program_entity, $sixth_program_entity, 2),
        ],
      ],
    ];
    $expected_relations = [
      // self-relation
      new ProjectRemixRelation($program_entity, $program_entity, 0),

      // relation to parents
      new ProjectRemixRelation($fifth_program_entity, $program_entity, 1),
      new ProjectRemixRelation($sixth_program_entity, $program_entity, 1),

      // relation to grandparents
      new ProjectRemixRelation($third_program_entity, $program_entity, 2),
      new ProjectRemixRelation($second_program_entity, $program_entity, 2),
      new ProjectRemixRelation($fourth_program_entity, $program_entity, 2),

      // relation to parents of grandparents
      new ProjectRemixRelation($first_program_entity, $program_entity, 3),
      new ProjectRemixRelation($second_program_entity, $program_entity, 3),
    ];
    $this->checkRemixRelations($program_entity, $parent_data, $expected_relations);
    $this->assertFalse($program_entity->isRemixRoot());
  }

  /**
   * @throws \Exception
   * @throws Exception
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

    $program_entities = $this->getProgramEntities(7);
    $first_program_entity = $program_entities[0];
    $second_program_entity = $program_entities[1];
    $third_program_entity = $program_entities[2];
    $fourth_program_entity = $program_entities[3];
    $fifth_program_entity = $program_entities[4];
    $sixth_program_entity = $program_entities[5];

    $program_entity = $program_entities[6];

    $parent_data = [
      $third_program_entity->getId() => [
        'isScratch' => false,
        'entity' => $third_program_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($third_program_entity, $third_program_entity, 0),
          new ProjectRemixRelation($first_program_entity, $third_program_entity, 1),
          new ProjectRemixRelation($second_program_entity, $third_program_entity, 1),
        ],
      ],
      $fifth_program_entity->getId() => [
        'isScratch' => false,
        'entity' => $fifth_program_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($fifth_program_entity, $fifth_program_entity, 0),
          new ProjectRemixRelation($third_program_entity, $fifth_program_entity, 1),
          new ProjectRemixRelation($second_program_entity, $fifth_program_entity, 1),
          new ProjectRemixRelation($fourth_program_entity, $fifth_program_entity, 1),
          new ProjectRemixRelation($first_program_entity, $fifth_program_entity, 2),
          new ProjectRemixRelation($second_program_entity, $fifth_program_entity, 2),
        ],
      ],
      $sixth_program_entity->getId() => [
        'isScratch' => false,
        'entity' => $sixth_program_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($sixth_program_entity, $sixth_program_entity, 0),
          new ProjectRemixRelation($fourth_program_entity, $sixth_program_entity, 1),
          new ProjectRemixRelation($second_program_entity, $sixth_program_entity, 1),
          new ProjectRemixRelation($second_program_entity, $sixth_program_entity, 2),
        ],
      ],
    ];
    $expected_relations = [
      // self-relation
      new ProjectRemixRelation($program_entity, $program_entity, 0),

      // relation to parents
      new ProjectRemixRelation($third_program_entity, $program_entity, 1),
      new ProjectRemixRelation($fifth_program_entity, $program_entity, 1),
      new ProjectRemixRelation($sixth_program_entity, $program_entity, 1),

      // relation to grandparents
      new ProjectRemixRelation($first_program_entity, $program_entity, 2),
      new ProjectRemixRelation($second_program_entity, $program_entity, 2),
      new ProjectRemixRelation($third_program_entity, $program_entity, 2),
      new ProjectRemixRelation($fourth_program_entity, $program_entity, 2),

      // relation to parents of grandparents
      new ProjectRemixRelation($first_program_entity, $program_entity, 3),
      new ProjectRemixRelation($second_program_entity, $program_entity, 3),
    ];
    $this->checkRemixRelations($program_entity, $parent_data, $expected_relations);
    $this->assertFalse($program_entity->isRemixRoot());
  }

  /**
   * @throws \Exception
   * @throws Exception
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

    $program_entities = $this->getProgramEntities(6);
    $first_program_entity = $program_entities[0];
    $second_program_entity = $program_entities[1];
    $third_program_entity = $program_entities[2];
    $fourth_program_entity = $program_entities[3];
    $fifth_program_entity = $program_entities[4];

    $program_entity = $program_entities[5];

    $parent_data = [
      $second_program_entity->getId() => [
        'isScratch' => false,
        'entity' => $second_program_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($second_program_entity, $second_program_entity, 0),
          new ProjectRemixRelation($first_program_entity, $second_program_entity, 1),
          new ScratchProjectRemixRelation($scratch_parent_id, $second_program_entity),
        ],
      ],
      $fourth_program_entity->getId() => [
        'isScratch' => false,
        'entity' => $fourth_program_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($fourth_program_entity, $fourth_program_entity, 0),
          new ProjectRemixRelation($third_program_entity, $fourth_program_entity, 1),
          new ProjectRemixRelation($second_program_entity, $fourth_program_entity, 1),
          new ScratchProjectRemixRelation($scratch_parent_id, $fourth_program_entity),
          new ProjectRemixRelation($first_program_entity, $fourth_program_entity, 2),
        ],
      ],
      $fifth_program_entity->getId() => [
        'isScratch' => false,
        'entity' => $fifth_program_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($fifth_program_entity, $fifth_program_entity, 0),
          new ProjectRemixRelation($third_program_entity, $fifth_program_entity, 1),
          new ScratchProjectRemixRelation($scratch_parent_id, $fifth_program_entity),
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
      new ProjectRemixRelation($program_entity, $program_entity, 0),

      // relation to parents
      new ProjectRemixRelation($second_program_entity, $program_entity, 1),
      new ProjectRemixRelation($fourth_program_entity, $program_entity, 1),
      new ProjectRemixRelation($fifth_program_entity, $program_entity, 1),
      new ScratchProjectRemixRelation($scratch_parent_id, $program_entity),

      // relation to grandparents
      new ProjectRemixRelation($first_program_entity, $program_entity, 2),
      new ProjectRemixRelation($second_program_entity, $program_entity, 2),
      new ProjectRemixRelation($third_program_entity, $program_entity, 2),

      // relation to parents of grandparents
      new ProjectRemixRelation($first_program_entity, $program_entity, 3),
    ];
    $this->checkRemixRelations($program_entity, $parent_data, $expected_relations);
    $this->assertFalse($program_entity->isRemixRoot());
  }

  /**
   * @throws \Exception
   * @throws Exception
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

    $program_entities = $this->getProgramEntities(5);
    $first_program_entity = $program_entities[0];
    $second_program_entity = $program_entities[1];
    $third_program_entity = $program_entities[2];
    $fourth_program_entity = $program_entities[3];

    $program_entity = $program_entities[4];

    $parent_data = [
      $first_program_entity->getId() => [
        'isScratch' => false,
        'entity' => $first_program_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($first_program_entity, $first_program_entity, 0),
          new ScratchProjectRemixRelation($first_scratch_ancestor_id, $first_program_entity),
          new ScratchProjectRemixRelation($second_scratch_parent_id, $first_program_entity),
        ],
      ],
      $third_program_entity->getId() => [
        'isScratch' => false,
        'entity' => $third_program_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($third_program_entity, $third_program_entity, 0),
          new ProjectRemixRelation($second_program_entity, $third_program_entity, 1),
          new ProjectRemixRelation($first_program_entity, $third_program_entity, 1),
          new ScratchProjectRemixRelation($second_scratch_parent_id, $third_program_entity),
        ],
      ],
      $fourth_program_entity->getId() => [
        'isScratch' => false,
        'entity' => $fourth_program_entity,
        'exists' => true,
        'existingRelations' => [
          new ProjectRemixRelation($fourth_program_entity, $fourth_program_entity, 0),
          new ProjectRemixRelation($second_program_entity, $fourth_program_entity, 1),
          new ScratchProjectRemixRelation($second_scratch_parent_id, $fourth_program_entity),
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
      new ProjectRemixRelation($program_entity, $program_entity, 0),

      // relation to parents
      new ProjectRemixRelation($first_program_entity, $program_entity, 1),
      new ProjectRemixRelation($third_program_entity, $program_entity, 1),
      new ProjectRemixRelation($fourth_program_entity, $program_entity, 1),
      new ScratchProjectRemixRelation($second_scratch_parent_id, $program_entity),

      // relation to grandparents
      new ProjectRemixRelation($first_program_entity, $program_entity, 2),
      new ProjectRemixRelation($second_program_entity, $program_entity, 2),
    ];
    $this->checkRemixRelations($program_entity, $parent_data, $expected_relations);
    $this->assertFalse($program_entity->isRemixRoot());
  }

  /**
   * @throws \Exception
   * @throws Exception
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

    $program_entities = $this->getProgramEntities(5);
    $first_program_entity = $program_entities[0];
    $second_program_entity = $program_entities[1];
    $third_program_entity = $program_entities[2];
    $fourth_program_entity = $program_entities[3];
    $program_entity = $program_entities[4];

    $existingRelationsFirstProgramEntity = [
      new ProjectRemixRelation($first_program_entity, $first_program_entity, 0),
      new ScratchProjectRemixRelation($first_scratch_ancestor_id, $first_program_entity),
      new ScratchProjectRemixRelation($second_scratch_parent_id, $first_program_entity),
    ];

    $existingRelationsThirdProgramEntity = [
      new ProjectRemixRelation($third_program_entity, $third_program_entity, 0),
      new ProjectRemixRelation($second_program_entity, $third_program_entity, 1),
      new ProjectRemixRelation($first_program_entity, $third_program_entity, 1),
      new ScratchProjectRemixRelation($second_scratch_parent_id, $third_program_entity),
    ];

    $existingRelationsFourthProgramEntity = [
      new ProjectRemixRelation($fourth_program_entity, $fourth_program_entity, 0),
      new ProjectRemixRelation($second_program_entity, $fourth_program_entity, 1),
      new ScratchProjectRemixRelation($second_scratch_parent_id, $fourth_program_entity),
    ];

    $parent_data = [
      $first_program_entity->getId() ?? 'first' => [
        'isScratch' => false,
        'entity' => $first_program_entity,
        'exists' => true,
        'existingRelations' => $existingRelationsFirstProgramEntity,
      ],
      $third_program_entity->getId() ?? 'third' => [
        'isScratch' => false,
        'entity' => $third_program_entity,
        'exists' => true,
        'existingRelations' => $existingRelationsThirdProgramEntity,
      ],
      $fourth_program_entity->getId() ?? 'fourth' => [
        'isScratch' => false,
        'entity' => $fourth_program_entity,
        'exists' => true,
        'existingRelations' => $existingRelationsFourthProgramEntity,
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
      new ProjectRemixRelation($program_entity, $program_entity, 0),

      // relation to parents
      new ProjectRemixRelation($first_program_entity, $program_entity, 1),
      new ProjectRemixRelation($third_program_entity, $program_entity, 1),
      new ProjectRemixRelation($fourth_program_entity, $program_entity, 1),
      new ScratchProjectRemixRelation($second_scratch_parent_id, $program_entity),
      new ScratchProjectRemixRelation($third_scratch_parent_id, $program_entity),

      // relation to grandparents
      new ProjectRemixRelation($first_program_entity, $program_entity, 2),
      new ProjectRemixRelation($second_program_entity, $program_entity, 2),
    ];
    $this->checkRemixRelations($program_entity, $parent_data, $expected_relations);
    $this->assertFalse($program_entity->isRemixRoot());
  }

  /**
   * @throws \Exception
   */
  private function checkRemixRelations(Project $program_entity, array $parent_data, array $expected_relations): void
  {
    $expected_relations_map = [];
    $expected_catrobat_relations = [];
    foreach ($expected_relations as $expected_relation) {
      if ($expected_relation instanceof ProjectRemixRelation) {
        $expected_catrobat_relations[] = $expected_relation;
      }

      $expected_relations_map[$expected_relation->getUniqueKey()] = $expected_relation;
    }

    $program_repository_find_map = [];
    $program_remix_repository_find_map = [];

    foreach ($parent_data as $parent_id => $data) {
      $catrobat_relations = array_filter($data['existingRelations'], static fn ($relation): bool => $relation instanceof ProjectRemixRelation);
      if ($data['exists']) {
        $program_remix_repository_find_map[(string) $parent_id] = $catrobat_relations;
      }

      $program_repository_find_map[$parent_id] = $data['exists'] ? $data['entity'] : null;
    }

    $this->program_repository
      ->method('find')
      ->willReturnCallback(static fn ($id) => $program_repository_find_map[$id] ?? null)
    ;

    $this->program_remix_repository
      ->method('findBy')
      ->willReturnCallback(static function (array $criteria) use ($program_remix_repository_find_map): array {
        $descendant_id = $criteria['descendant_id'] ?? null;

        return $program_remix_repository_find_map[$descendant_id] ?? [];
      })
    ;

    $this->entity_manager
      ->expects($this->atLeastOnce())
      ->method('persist')
      ->willReturnCallback(static function ($arg) use ($program_entity, &$expected_relations_map): void {
        if ($arg instanceof ProjectRemixRelation || $arg instanceof ScratchProjectRemixRelation) {
          $relation = $arg;
          Assert::assertArrayHasKey($relation->getUniqueKey(), $expected_relations_map);
          unset($expected_relations_map[$relation->getUniqueKey()]);
        }
        if ($arg instanceof Project) {
          Assert::assertEquals($arg, $program_entity);
        }
      })
    ;

    $this->entity_manager->expects($this->atLeastOnce())->method('flush');

    $remixes_data = [];
    foreach ($parent_data as $parent_id => $data) {
      $remixes_data[] = new RemixData($data['isScratch'] ? 'https://scratch.mit.edu/projects/'.$parent_id.'/'
        : '/app/project/'.$parent_id);
    }

    Assert::assertCount(count($expected_relations), $expected_relations_map);

    $expected_to_be_root = (1 === count($expected_catrobat_relations));
    assert($program_entity instanceof MockObject);
    $program_entity->expects($this->atLeastOnce())
      ->method('isRemixRoot')->willReturn($expected_to_be_root)
    ;
    $this->remix_manager->addRemixes($program_entity, $remixes_data);
    Assert::assertCount(0, $expected_relations_map);
  }

  /**
   * @throws Exception
   */
  private function getProgramEntities(int $amount): array
  {
    $array = [];
    for ($i = 1; $i <= $amount; ++$i) {
      $program_entity = $this->createMock(Project::class);
      $program_entity->expects($this->atLeastOnce())
        ->method('getId')
        ->willReturn(strval($i))
      ;
      $program_entity->method('getUser')->willReturn($this->createMock(User::class));
      if ($i === $amount) {
        $program_entity->expects($this->atLeastOnce())
          ->method('isInitialVersion')
          ->willReturn(true)
        ;
      }

      $array[] = $program_entity;
    }

    return $array;
  }

  /**
   * @throws Exception
   */
  private function getProgramEntityAndParents(string $entityReturn = '123', string $firstParentReturn = '3570', string $secParentReturn = '16267'): array
  {
    $program_entity = $this->createMock(Project::class);
    $program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn($entityReturn)
    ;
    $program_entity->expects($this->atLeastOnce())
      ->method('isInitialVersion')->willReturn(true)
    ;
    $program_entity->method('getUser')->willReturn($this->createMock(User::class));

    $first_parent_entity = $this->createMock(Project::class);
    $first_parent_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn($firstParentReturn)
    ;
    $first_parent_entity->method('getUser')->willReturn($this->createMock(User::class));

    $second_parent_entity = $this->createMock(Project::class);
    $second_parent_entity->expects($this->atLeastOnce())
      ->method('getId')
      ->willReturn($secParentReturn)
    ;
    $second_parent_entity->method('getUser')->willReturn($this->createMock(User::class));

    $array = [];
    $array[] = $program_entity;
    $array[] = $first_parent_entity;
    $array[] = $second_parent_entity;

    return $array;
  }
}
