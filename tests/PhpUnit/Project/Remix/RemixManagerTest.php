<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\Remix;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\Remix\ProgramRemixRelation;
use App\DB\Entity\Project\Scratch\ScratchProgram;
use App\DB\Entity\Project\Scratch\ScratchProgramRemixRelation;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Project\ProgramRemixBackwardRepository;
use App\DB\EntityRepository\Project\ProgramRemixRepository;
use App\DB\EntityRepository\Project\ProgramRepository;
use App\DB\EntityRepository\Project\ScratchProgramRemixRepository;
use App\DB\EntityRepository\Project\ScratchProgramRepository;
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

  private MockObject|ProgramRepository $program_repository;

  private MockObject|ScratchProgramRepository $scratch_program_repository;

  private MockObject|ProgramRemixRepository $program_remix_repository;

  protected function setUp(): void
  {
    $this->entity_manager = $this->createMock(EntityManager::class);
    $this->program_repository = $this->createMock(ProgramRepository::class);
    $this->scratch_program_repository = $this->createMock(ScratchProgramRepository::class);
    $this->program_remix_repository = $this->createMock(ProgramRemixRepository::class);
    $program_remix_backward_repository = $this->createMock(ProgramRemixBackwardRepository::class);
    $scratch_program_remix_repository = $this->createMock(ScratchProgramRemixRepository::class);
    $remix_graph_manipulator = $this->createMock(RemixGraphManipulator::class);
    $catro_notification_service = $this->createMock(NotificationManager::class);
    $this->remix_manager = new RemixManager($this->entity_manager, $this->program_repository, $this->scratch_program_repository, $this->program_remix_repository, $program_remix_backward_repository, $scratch_program_remix_repository, $remix_graph_manipulator, $catro_notification_service);
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(RemixManager::class, $this->remix_manager);
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
      ->method('persist')->with($this->isInstanceOf(ScratchProgram::class))
      ->will($this->returnCallback(function (ScratchProgram $scratch_project) use (
        $expected_id_of_first_program, $expected_name_of_first_program,
        $expected_description_of_first_program, $expected_username_of_first_program
      ) {
        $this->assertInstanceOf(ScratchProgram::class, $scratch_project);
        $this->assertSame($expected_id_of_first_program, $scratch_project->getId());
        $this->assertSame($expected_name_of_first_program, $scratch_project->getName());
        $this->assertSame($expected_description_of_first_program, $scratch_project->getDescription());
        $this->assertSame($expected_username_of_first_program, $scratch_project->getUsername());
      }))
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
      ->method('persist')->with($this->isInstanceOf(ScratchProgram::class))
      ->will($this->returnCallback(function (ScratchProgram $scratch_project) use ($expected_id_of_first_program) {
        $this->assertInstanceOf(ScratchProgram::class, $scratch_project);
        $this->assertSame($expected_id_of_first_program, $scratch_project->getId());
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
      ->method('persist')->with($this->isInstanceOf(ScratchProgram::class))
      ->will($this->returnCallback(function (ScratchProgram $scratch_project) use (
        $expected_id_of_first_program, $expected_name_of_first_program,
        $expected_description_of_first_program, $expected_username_of_first_program,
        $expected_id_of_second_program, $expected_name_of_second_program, $expected_username_of_second_program
      ) {
        $this->assertInstanceOf(ScratchProgram::class, $scratch_project);
        if ($scratch_project->getId() === $expected_id_of_first_program) {
          $this->assertSame($expected_name_of_first_program, $scratch_project->getName());
          $this->assertSame($expected_description_of_first_program, $scratch_project->getDescription());
          $this->assertSame($expected_username_of_first_program, $scratch_project->getUsername());
        } elseif ($scratch_project->getId() === $expected_id_of_second_program) {
          $this->assertSame($expected_name_of_second_program, $scratch_project->getName());
          $this->assertNull($scratch_project->getDescription());
          $this->assertSame($expected_username_of_second_program, $scratch_project->getUsername());
        }
      }))
    ;

    $this->entity_manager->expects($this->atLeastOnce())->method('flush');
    $this->remix_manager->addScratchProjects($scratch_info_data);
  }

  /**
   * @throws \Exception
   */
  public function testSetProgramAsRootAndDontAddRemixRelationsWhenNoParentsAreGiven(): void
  {
    $program_entity = $this->createMock(Program::class);
    $program_entity->expects($this->atLeastOnce())->method('getId')->willReturn('123');
    $program_entity->expects($this->atLeastOnce())->method('isInitialVersion')->willReturn(true);

    $parent_data = [];
    $expected_relations = [
      new ProgramRemixRelation($program_entity, $program_entity, 0),
    ];

    $this->checkRemixRelations($program_entity, $parent_data, $expected_relations);
    $this->assertTrue($program_entity->isRemixRoot());
  }

  /**
   * @throws \Exception
   */
  public function testSetProgramAsRootAndDontAddRemixRelationsForNonExistingParents(): void
  {
    $program_entity = $this->createMock(Program::class);
    $program_entity->expects($this->atLeastOnce())->method('getId')->willReturn('123');
    $program_entity->expects($this->atLeastOnce())->method('isInitialVersion')->willReturn(true);

    $first_parent_entity = $this->createMock(Program::class);
    $first_parent_entity->expects($this->atLeastOnce())->method('getId')->willReturn('3570');
    $first_parent_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;

    $second_parent_entity = $this->createMock(Program::class);
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
      new ProgramRemixRelation($program_entity, $program_entity, 0),
    ];

    $this->checkRemixRelations($program_entity, $parent_data, $expected_relations);
    $this->assertTrue($program_entity->isRemixRoot());
  }

  /**
   * @throws \Exception
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
    $program_entity = $this->createMock(Program::class);
    $program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('123')
    ;
    $program_entity->expects($this->atLeastOnce())
      ->method('isInitialVersion')->willReturn(true)
    ;
    $program_entity->expects($this->any())
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
      new ProgramRemixRelation($program_entity, $program_entity, 0),
      new ScratchProgramRemixRelation($first_scratch_parent_id, $program_entity),
      new ScratchProgramRemixRelation($second_scratch_parent_id, $program_entity),
    ];

    $this->checkRemixRelations($program_entity, $parent_data, $expected_relations);
    $this->assertTrue($program_entity->isRemixRoot());
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

    $program_entities = $this->getProgramEntityAndParents('2');

    /** @var Program $program_entity */
    $program_entity = $program_entities[0];

    /** @var Program $first_parent_entity */
    $first_parent_entity = $program_entities[1];

    /** @var Program $second_parent_entity */
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
      new ProgramRemixRelation($program_entity, $program_entity, 0),
      new ProgramRemixRelation($second_parent_entity, $program_entity, 1),
    ];
    $this->checkRemixRelations($program_entity, $parent_data, $expected_relations);
    $this->assertFalse($program_entity->isRemixRoot());
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
      new ProgramRemixRelation($program_entity, $program_entity, 0),
      new ProgramRemixRelation($first_parent_entity, $program_entity, 1),
      new ProgramRemixRelation($second_parent_entity, $program_entity, 1),
    ];
    $this->checkRemixRelations($program_entity, $parent_data, $expected_relations);
    $this->assertFalse($program_entity->isRemixRoot());
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

    $parent_entity_of_both_parents = $this->createMock(Program::class);
    $parent_entity_of_both_parents->expects($this->atLeastOnce())
      ->method('getId')->willReturn('1')
    ;
    $parent_entity_of_both_parents->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;

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
          new ProgramRemixRelation($first_parent_entity, $first_parent_entity, 0),
          new ProgramRemixRelation($parent_entity_of_both_parents, $first_parent_entity, 1),
        ],
      ],
      $second_parent_entity->getId() => [
        'isScratch' => false,
        'entity' => $second_parent_entity,
        'exists' => true,
        'existingRelations' => [
          new ProgramRemixRelation($second_parent_entity, $second_parent_entity, 0),
          new ProgramRemixRelation($parent_entity_of_both_parents, $second_parent_entity, 1),
        ],
      ],
    ];

    $expected_relations = [
      // self-relation
      new ProgramRemixRelation($program_entity, $program_entity, 0),

      // relation to parents
      new ProgramRemixRelation($first_parent_entity, $program_entity, 1),
      new ProgramRemixRelation($second_parent_entity, $program_entity, 1),

      // relation to grandparents
      new ProgramRemixRelation($parent_entity_of_both_parents, $program_entity, 2),
    ];
    $this->checkRemixRelations($program_entity, $parent_data, $expected_relations);
    $this->assertFalse($program_entity->isRemixRoot());
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
    $parent_entity_of_first_parent = $this->createMock(Program::class);
    $parent_entity_of_first_parent->expects($this->atLeastOnce())
      ->method('getId')->willReturn('1')
    ;
    $parent_entity_of_first_parent->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $parent_entity_of_second_parent = $this->createMock(Program::class);
    $parent_entity_of_second_parent->expects($this->atLeastOnce())
      ->method('getId')->willReturn('2')
    ;
    $parent_entity_of_second_parent->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $first_parent_entity = $this->createMock(Program::class);
    $first_parent_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('3')
    ;
    $first_parent_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $second_parent_entity = $this->createMock(Program::class);
    $second_parent_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('4')
    ;
    $second_parent_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $program_entity = $this->createMock(Program::class);
    $program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('5')
    ;
    $program_entity->expects($this->atLeastOnce())
      ->method('isInitialVersion')->willReturn(true)
    ;
    $program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $parent_data = [
      $first_parent_entity->getId() ?? 'first' => [
        'isScratch' => false,
        'entity' => $first_parent_entity,
        'exists' => true,
        'existingRelations' => [
          new ProgramRemixRelation($first_parent_entity, $first_parent_entity, 0),
          new ProgramRemixRelation($parent_entity_of_first_parent, $first_parent_entity, 1),
        ],
      ],
      $second_parent_entity->getId() ?? 'second' => [
        'isScratch' => false,
        'entity' => $second_parent_entity,
        'exists' => true,
        'existingRelations' => [
          new ProgramRemixRelation($second_parent_entity, $second_parent_entity, 0),
          new ProgramRemixRelation($parent_entity_of_second_parent, $second_parent_entity, 1),
        ],
      ],
    ];
    $expected_relations = [
      // self-relation
      new ProgramRemixRelation($program_entity, $program_entity, 0),

      // relation to parents
      new ProgramRemixRelation($first_parent_entity, $program_entity, 1),
      new ProgramRemixRelation($second_parent_entity, $program_entity, 1),

      // relation to grandparents
      new ProgramRemixRelation($parent_entity_of_first_parent, $program_entity, 2),
      new ProgramRemixRelation($parent_entity_of_second_parent, $program_entity, 2),
    ];
    $this->checkRemixRelations($program_entity, $parent_data, $expected_relations);
    $this->assertFalse($program_entity->isRemixRoot());
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

    $parent_entity_of_first_parent = $this->createMock(Program::class);
    $parent_entity_of_first_parent->expects($this->atLeastOnce())
      ->method('getId')->willReturn('1')
    ;
    $parent_entity_of_first_parent->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;

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
          new ProgramRemixRelation($first_parent_entity, $first_parent_entity, 0),
          new ProgramRemixRelation($parent_entity_of_first_parent, $first_parent_entity, 1),
        ],
      ],
      $second_parent_entity->getId() => [
        'isScratch' => false,
        'entity' => $second_parent_entity,
        'exists' => true,
        'existingRelations' => [
          new ProgramRemixRelation($second_parent_entity, $second_parent_entity, 0),
          new ScratchProgramRemixRelation($scratch_parent_id, $second_parent_entity),
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
      new ProgramRemixRelation($program_entity, $program_entity, 0),

      // relation to parents
      new ProgramRemixRelation($first_parent_entity, $program_entity, 1),
      new ProgramRemixRelation($second_parent_entity, $program_entity, 1),
      new ScratchProgramRemixRelation($scratch_parent_id, $program_entity),

      // relation to grandparents
      new ProgramRemixRelation($parent_entity_of_first_parent, $program_entity, 2),
    ];
    $this->checkRemixRelations($program_entity, $parent_data, $expected_relations);
    $this->assertFalse($program_entity->isRemixRoot());
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
          new ProgramRemixRelation($third_program_entity, $third_program_entity, 0),
          new ProgramRemixRelation($first_program_entity, $third_program_entity, 1),
          new ProgramRemixRelation($second_program_entity, $third_program_entity, 1),
        ],
      ],
      $fourth_program_entity->getId() => [
        'isScratch' => false,
        'entity' => $fourth_program_entity,
        'exists' => true,
        'existingRelations' => [
          new ProgramRemixRelation($fourth_program_entity, $fourth_program_entity, 0),
          new ProgramRemixRelation($second_program_entity, $fourth_program_entity, 1),
        ],
      ],
    ];
    $expected_relations = [
      // self-relation
      new ProgramRemixRelation($program_entity, $program_entity, 0),

      // relation to parents
      new ProgramRemixRelation($third_program_entity, $program_entity, 1),
      new ProgramRemixRelation($fourth_program_entity, $program_entity, 1),

      // relation to grandparents
      new ProgramRemixRelation($first_program_entity, $program_entity, 2),
      new ProgramRemixRelation($second_program_entity, $program_entity, 2),
    ];
    $this->checkRemixRelations($program_entity, $parent_data, $expected_relations);
    $this->assertFalse($program_entity->isRemixRoot());
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
          new ProgramRemixRelation($fifth_program_entity, $fifth_program_entity, 0),
          new ProgramRemixRelation($third_program_entity, $fifth_program_entity, 1),
          new ProgramRemixRelation($fourth_program_entity, $fifth_program_entity, 1),
          new ProgramRemixRelation($first_program_entity, $fifth_program_entity, 2),
          new ProgramRemixRelation($second_program_entity, $fifth_program_entity, 2),
        ],
      ],
      $sixth_program_entity->getId() => [
        'isScratch' => false,
        'entity' => $sixth_program_entity,
        'exists' => true,
        'existingRelations' => [
          new ProgramRemixRelation($sixth_program_entity, $sixth_program_entity, 0),
          new ProgramRemixRelation($fourth_program_entity, $sixth_program_entity, 1),
          new ProgramRemixRelation($second_program_entity, $sixth_program_entity, 2),
        ],
      ],
    ];
    $expected_relations = [
      // self-relation
      new ProgramRemixRelation($program_entity, $program_entity, 0),

      // relation to parents
      new ProgramRemixRelation($fifth_program_entity, $program_entity, 1),
      new ProgramRemixRelation($sixth_program_entity, $program_entity, 1),

      // relation to grandparents
      new ProgramRemixRelation($third_program_entity, $program_entity, 2),
      new ProgramRemixRelation($fourth_program_entity, $program_entity, 2),

      // relation to parents of grandparents
      new ProgramRemixRelation($first_program_entity, $program_entity, 3),
      new ProgramRemixRelation($second_program_entity, $program_entity, 3),
    ];
    $this->checkRemixRelations($program_entity, $parent_data, $expected_relations);
    $this->assertFalse($program_entity->isRemixRoot());
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
          new ProgramRemixRelation($fifth_program_entity, $fifth_program_entity, 0),
          new ProgramRemixRelation($third_program_entity, $fifth_program_entity, 1),
          new ProgramRemixRelation($second_program_entity, $fifth_program_entity, 1),
          new ProgramRemixRelation($first_program_entity, $fifth_program_entity, 2),
          new ProgramRemixRelation($second_program_entity, $fifth_program_entity, 2),
        ],
      ],
      $sixth_program_entity->getId() => [
        'isScratch' => false,
        'entity' => $sixth_program_entity,
        'exists' => true,
        'existingRelations' => [
          new ProgramRemixRelation($sixth_program_entity, $sixth_program_entity, 0),
          new ProgramRemixRelation($fourth_program_entity, $sixth_program_entity, 1),
          new ProgramRemixRelation($second_program_entity, $sixth_program_entity, 2),
        ],
      ],
    ];
    $expected_relations = [
      // self-relation
      new ProgramRemixRelation($program_entity, $program_entity, 0),

      // relation to parents
      new ProgramRemixRelation($fifth_program_entity, $program_entity, 1),
      new ProgramRemixRelation($sixth_program_entity, $program_entity, 1),

      // relation to grandparents
      new ProgramRemixRelation($third_program_entity, $program_entity, 2),
      new ProgramRemixRelation($second_program_entity, $program_entity, 2),
      new ProgramRemixRelation($fourth_program_entity, $program_entity, 2),

      // relation to parents of grandparents
      new ProgramRemixRelation($first_program_entity, $program_entity, 3),
      new ProgramRemixRelation($second_program_entity, $program_entity, 3),
    ];
    $this->checkRemixRelations($program_entity, $parent_data, $expected_relations);
    $this->assertFalse($program_entity->isRemixRoot());
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
          new ProgramRemixRelation($third_program_entity, $third_program_entity, 0),
          new ProgramRemixRelation($first_program_entity, $third_program_entity, 1),
          new ProgramRemixRelation($second_program_entity, $third_program_entity, 1),
        ],
      ],
      $fifth_program_entity->getId() => [
        'isScratch' => false,
        'entity' => $fifth_program_entity,
        'exists' => true,
        'existingRelations' => [
          new ProgramRemixRelation($fifth_program_entity, $fifth_program_entity, 0),
          new ProgramRemixRelation($third_program_entity, $fifth_program_entity, 1),
          new ProgramRemixRelation($second_program_entity, $fifth_program_entity, 1),
          new ProgramRemixRelation($fourth_program_entity, $fifth_program_entity, 1),
          new ProgramRemixRelation($first_program_entity, $fifth_program_entity, 2),
          new ProgramRemixRelation($second_program_entity, $fifth_program_entity, 2),
        ],
      ],
      $sixth_program_entity->getId() => [
        'isScratch' => false,
        'entity' => $sixth_program_entity,
        'exists' => true,
        'existingRelations' => [
          new ProgramRemixRelation($sixth_program_entity, $sixth_program_entity, 0),
          new ProgramRemixRelation($fourth_program_entity, $sixth_program_entity, 1),
          new ProgramRemixRelation($second_program_entity, $sixth_program_entity, 1),
          new ProgramRemixRelation($second_program_entity, $sixth_program_entity, 2),
        ],
      ],
    ];
    $expected_relations = [
      // self-relation
      new ProgramRemixRelation($program_entity, $program_entity, 0),

      // relation to parents
      new ProgramRemixRelation($third_program_entity, $program_entity, 1),
      new ProgramRemixRelation($fifth_program_entity, $program_entity, 1),
      new ProgramRemixRelation($sixth_program_entity, $program_entity, 1),

      // relation to grandparents
      new ProgramRemixRelation($first_program_entity, $program_entity, 2),
      new ProgramRemixRelation($second_program_entity, $program_entity, 2),
      new ProgramRemixRelation($third_program_entity, $program_entity, 2),
      new ProgramRemixRelation($fourth_program_entity, $program_entity, 2),

      // relation to parents of grandparents
      new ProgramRemixRelation($first_program_entity, $program_entity, 3),
      new ProgramRemixRelation($second_program_entity, $program_entity, 3),
    ];
    $this->checkRemixRelations($program_entity, $parent_data, $expected_relations);
    $this->assertFalse($program_entity->isRemixRoot());
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
          new ProgramRemixRelation($second_program_entity, $second_program_entity, 0),
          new ProgramRemixRelation($first_program_entity, $second_program_entity, 1),
          new ScratchProgramRemixRelation($scratch_parent_id, $second_program_entity),
        ],
      ],
      $fourth_program_entity->getId() => [
        'isScratch' => false,
        'entity' => $fourth_program_entity,
        'exists' => true,
        'existingRelations' => [
          new ProgramRemixRelation($fourth_program_entity, $fourth_program_entity, 0),
          new ProgramRemixRelation($third_program_entity, $fourth_program_entity, 1),
          new ProgramRemixRelation($second_program_entity, $fourth_program_entity, 1),
          new ScratchProgramRemixRelation($scratch_parent_id, $fourth_program_entity),
          new ProgramRemixRelation($first_program_entity, $fourth_program_entity, 2),
        ],
      ],
      $fifth_program_entity->getId() => [
        'isScratch' => false,
        'entity' => $fifth_program_entity,
        'exists' => true,
        'existingRelations' => [
          new ProgramRemixRelation($fifth_program_entity, $fifth_program_entity, 0),
          new ProgramRemixRelation($third_program_entity, $fifth_program_entity, 1),
          new ScratchProgramRemixRelation($scratch_parent_id, $fifth_program_entity),
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
      new ProgramRemixRelation($program_entity, $program_entity, 0),

      // relation to parents
      new ProgramRemixRelation($second_program_entity, $program_entity, 1),
      new ProgramRemixRelation($fourth_program_entity, $program_entity, 1),
      new ProgramRemixRelation($fifth_program_entity, $program_entity, 1),
      new ScratchProgramRemixRelation($scratch_parent_id, $program_entity),

      // relation to grandparents
      new ProgramRemixRelation($first_program_entity, $program_entity, 2),
      new ProgramRemixRelation($second_program_entity, $program_entity, 2),
      new ProgramRemixRelation($third_program_entity, $program_entity, 2),

      // relation to parents of grandparents
      new ProgramRemixRelation($first_program_entity, $program_entity, 3),
    ];
    $this->checkRemixRelations($program_entity, $parent_data, $expected_relations);
    $this->assertFalse($program_entity->isRemixRoot());
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
          new ProgramRemixRelation($first_program_entity, $first_program_entity, 0),
          new ScratchProgramRemixRelation($first_scratch_ancestor_id, $first_program_entity),
          new ScratchProgramRemixRelation($second_scratch_parent_id, $first_program_entity),
        ],
      ],
      $third_program_entity->getId() => [
        'isScratch' => false,
        'entity' => $third_program_entity,
        'exists' => true,
        'existingRelations' => [
          new ProgramRemixRelation($third_program_entity, $third_program_entity, 0),
          new ProgramRemixRelation($second_program_entity, $third_program_entity, 1),
          new ProgramRemixRelation($first_program_entity, $third_program_entity, 1),
          new ScratchProgramRemixRelation($second_scratch_parent_id, $third_program_entity),
        ],
      ],
      $fourth_program_entity->getId() => [
        'isScratch' => false,
        'entity' => $fourth_program_entity,
        'exists' => true,
        'existingRelations' => [
          new ProgramRemixRelation($fourth_program_entity, $fourth_program_entity, 0),
          new ProgramRemixRelation($second_program_entity, $fourth_program_entity, 1),
          new ScratchProgramRemixRelation($second_scratch_parent_id, $fourth_program_entity),
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
      new ProgramRemixRelation($program_entity, $program_entity, 0),

      // relation to parents
      new ProgramRemixRelation($first_program_entity, $program_entity, 1),
      new ProgramRemixRelation($third_program_entity, $program_entity, 1),
      new ProgramRemixRelation($fourth_program_entity, $program_entity, 1),
      new ScratchProgramRemixRelation($second_scratch_parent_id, $program_entity),

      // relation to grandparents
      new ProgramRemixRelation($first_program_entity, $program_entity, 2),
      new ProgramRemixRelation($second_program_entity, $program_entity, 2),
    ];
    $this->checkRemixRelations($program_entity, $parent_data, $expected_relations);
    $this->assertFalse($program_entity->isRemixRoot());
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

    $program_entities = $this->getProgramEntities(5);
    $first_program_entity = $program_entities[0];
    $second_program_entity = $program_entities[1];
    $third_program_entity = $program_entities[2];
    $fourth_program_entity = $program_entities[3];
    $program_entity = $program_entities[4];

    $existingRelationsFirstProgramEntity = [
      new ProgramRemixRelation($first_program_entity, $first_program_entity, 0),
      new ScratchProgramRemixRelation($first_scratch_ancestor_id, $first_program_entity),
      new ScratchProgramRemixRelation($second_scratch_parent_id, $first_program_entity),
    ];

    $existingRelationsThirdProgramEntity = [
      new ProgramRemixRelation($third_program_entity, $third_program_entity, 0),
      new ProgramRemixRelation($second_program_entity, $third_program_entity, 1),
      new ProgramRemixRelation($first_program_entity, $third_program_entity, 1),
      new ScratchProgramRemixRelation($second_scratch_parent_id, $third_program_entity),
    ];

    $existingRelationsFourthProgramEntity = [
      new ProgramRemixRelation($fourth_program_entity, $fourth_program_entity, 0),
      new ProgramRemixRelation($second_program_entity, $fourth_program_entity, 1),
      new ScratchProgramRemixRelation($second_scratch_parent_id, $fourth_program_entity),
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
      new ProgramRemixRelation($program_entity, $program_entity, 0),

      // relation to parents
      new ProgramRemixRelation($first_program_entity, $program_entity, 1),
      new ProgramRemixRelation($third_program_entity, $program_entity, 1),
      new ProgramRemixRelation($fourth_program_entity, $program_entity, 1),
      new ScratchProgramRemixRelation($second_scratch_parent_id, $program_entity),
      new ScratchProgramRemixRelation($third_scratch_parent_id, $program_entity),

      // relation to grandparents
      new ProgramRemixRelation($first_program_entity, $program_entity, 2),
      new ProgramRemixRelation($second_program_entity, $program_entity, 2),
    ];
    $this->checkRemixRelations($program_entity, $parent_data, $expected_relations);
    $this->assertFalse($program_entity->isRemixRoot());
  }

  /**
   * @param Program|MockObject $program_entity
   *
   * @throws \Exception
   */
  private function checkRemixRelations($program_entity, array $parent_data, array $expected_relations): void
  {
    /** @var MockObject|Program $program_entity */
    $expected_relations_map = [];
    $expected_catrobat_relations = [];
    foreach ($expected_relations as $expected_relation) {
      if ($expected_relation instanceof ProgramRemixRelation) {
        $expected_catrobat_relations[] = $expected_relation;
      }
      $expected_relations_map[$expected_relation->getUniqueKey()] = $expected_relation;
    }

    $program_repository_find_map = [];
    $program_remix_repository_find_map = [];

    foreach ($parent_data as $parent_id => $data) {
      $catrobat_relations = array_filter($data['existingRelations'], fn ($relation) => $relation instanceof ProgramRemixRelation);
      if ($data['exists']) {
        $program_remix_repository_find_map[(string) $parent_id] = $catrobat_relations;
      }
      $program_repository_find_map[$parent_id] = $data['exists'] ? $data['entity'] : null;
    }

    $this->program_repository
      ->expects($this->any())
      ->method('find')
      ->will($this->returnCallback(function ($id) use ($program_repository_find_map) {
        return $program_repository_find_map[$id] ?? null;
      }))
    ;

    $this->program_remix_repository
      ->expects($this->any())
      ->method('findBy')
      ->will($this->returnCallback(function ($criteria) use ($program_remix_repository_find_map) {
        $descendant_id = $criteria['descendant_id'] ?? null;

        return $program_remix_repository_find_map[$descendant_id] ?? [];
      }))
    ;

    $this->entity_manager
      ->expects($this->atLeastOnce())
      ->method('persist')
      ->will($this->returnCallback(function ($arg) use ($program_entity, &$expected_relations_map) {
        if ($arg instanceof ProgramRemixRelation || $arg instanceof ScratchProgramRemixRelation) {
          $relation = $arg;
          Assert::assertArrayHasKey($relation->getUniqueKey(), $expected_relations_map);
          unset($expected_relations_map[$relation->getUniqueKey()]);
        }

        if ($arg instanceof Program) {
          Assert::assertEquals($arg, $program_entity);
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
    $program_entity->expects($this->atLeastOnce())
      ->method('isRemixRoot')->willReturn($expected_to_be_root)
    ;
    $this->remix_manager->addRemixes($program_entity, $remixes_data);
    Assert::assertCount(0, $expected_relations_map);
  }

  private function getProgramEntities(int $amount): array
  {
    $array = [];
    for ($i = 1; $i <= $amount; ++$i) {
      $program_entity = $this->createMock(Program::class);
      $program_entity->expects($this->atLeastOnce())
        ->method('getId')
        ->willReturn(strval($i))
      ;
      $program_entity->expects($this->any())
        ->method('getUser')
        ->willReturn($this->createMock(User::class))
      ;
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

  private function getProgramEntityAndParents(string $entityReturn = '123', string $firstParentReturn = '3570', string $secParentReturn = '16267'): array
  {
    $program_entity = $this->createMock(Program::class);
    $program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn($entityReturn)
    ;
    $program_entity->expects($this->atLeastOnce())
      ->method('isInitialVersion')->willReturn(true)
    ;
    $program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;

    $first_parent_entity = $this->createMock(Program::class);
    $first_parent_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn($firstParentReturn)
    ;
    $first_parent_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;

    $second_parent_entity = $this->createMock(Program::class);
    $second_parent_entity->expects($this->atLeastOnce())
      ->method('getId')
      ->willReturn($secParentReturn)
    ;
    $second_parent_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;

    $array = [];
    array_push($array, $program_entity, $first_parent_entity, $second_parent_entity);

    return $array;
  }
}
