<?php

namespace Tests\phpUnit\Entity;

use App\Catrobat\RemixGraph\RemixGraphManipulator;
use App\Catrobat\Requests\AppRequest;
use App\Catrobat\Services\CatroNotificationService;
use App\Catrobat\Services\RemixData;
use App\Entity\Program;
use App\Entity\ProgramRemixRelation;
use App\Entity\RemixManager;
use App\Entity\ScratchProgram;
use App\Entity\ScratchProgramRemixRelation;
use App\Entity\User;
use App\Repository\ProgramRemixBackwardRepository;
use App\Repository\ProgramRemixRepository;
use App\Repository\ProgramRepository;
use App\Repository\ScratchProgramRemixRepository;
use App\Repository\ScratchProgramRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \App\Entity\RemixManager
 */
class RemixManagerTest extends TestCase
{
  private RemixManager $remix_manager;

  /**
   * @var EntityManager|MockObject
   */
  private $entity_manager;

  /**
   * @var MockObject|ProgramRepository
   */
  private $program_repository;

  /**
   * @var MockObject|ScratchProgramRepository
   */
  private $scratch_program_repository;

  /**
   * @var MockObject|ProgramRemixRepository
   */
  private $program_remix_repository;

  protected function setUp(): void
  {
    $this->entity_manager = $this->createMock(EntityManager::class);
    $this->program_repository = $this->createMock(ProgramRepository::class);
    $this->scratch_program_repository = $this->createMock(ScratchProgramRepository::class);
    $this->program_remix_repository = $this->createMock(ProgramRemixRepository::class);
    $program_remix_backward_repository = $this->createMock(ProgramRemixBackwardRepository::class);
    $scratch_program_remix_repository = $this->createMock(ScratchProgramRemixRepository::class);
    $remix_graph_manipulator = $this->createMock(RemixGraphManipulator::class);
    $app_request = $this->createMock(AppRequest::class);
    $catro_notification_service = $this->createMock(CatroNotificationService::class);
    $this->remix_manager = new RemixManager($this->entity_manager, $this->program_repository, $this->scratch_program_repository, $this->program_remix_repository, $program_remix_backward_repository, $scratch_program_remix_repository, $remix_graph_manipulator, $app_request, $catro_notification_service);
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(RemixManager::class, $this->remix_manager);
  }

  /**
   * @throws Exception
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
    $this->remix_manager->addScratchPrograms($scratch_info_data);
  }

  /**
   * @throws Exception
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
      ->will($this->returnCallback(function (ScratchProgram $scratch_project) use ($expected_id_of_first_program)
      {
        $this->assertInstanceOf(ScratchProgram::class, $scratch_project);
        $this->assertSame($expected_id_of_first_program, $scratch_project->getId());
        $this->assertNull($scratch_project->getName());
        $this->assertNull($scratch_project->getDescription());
        $this->assertNull($scratch_project->getUsername());
      }))
    ;

    $this->entity_manager->expects($this->atLeastOnce())->method('flush');
    $this->remix_manager->addScratchPrograms($scratch_info_data);
  }

  /**
   * @throws Exception
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
      ->expects($this->at(0))
      ->method('find')->with($expected_id_of_first_program)
      ->willReturn(null)
    ;

    $this->scratch_program_repository
      ->expects($this->at(1))
      ->method('find')->with($expected_id_of_second_program)
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
        if ($scratch_project->getId() === $expected_id_of_first_program)
        {
          $this->assertSame($expected_name_of_first_program, $scratch_project->getName());
          $this->assertSame($expected_description_of_first_program, $scratch_project->getDescription());
          $this->assertSame($expected_username_of_first_program, $scratch_project->getUsername());
        }
        elseif ($scratch_project->getId() === $expected_id_of_second_program)
        {
          $this->assertSame($expected_name_of_second_program, $scratch_project->getName());
          $this->assertNull($scratch_project->getDescription());
          $this->assertSame($expected_username_of_second_program, $scratch_project->getUsername());
        }
      }))
    ;

    $this->entity_manager->expects($this->atLeastOnce())->method('flush');
    $this->remix_manager->addScratchPrograms($scratch_info_data);
  }

  /**
   * @throws ORMException
   * @throws OptimisticLockException
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
   * @throws ORMException
   * @throws OptimisticLockException
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
      $first_parent_entity->getId() => [
        'isScratch' => false,
        'entity' => $first_parent_entity,
        'exists' => false,
        'existingRelations' => [],
      ],
      $second_parent_entity->getId() => [
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
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function testSetProgramAsRootIfOnlyHasScratchParents(): void
  {
    //--------------------------------------------------------------------------------------------------------------
    //
    //    (Scratch #1)   (Scratch #2)
    //         \             /
    //          \           /
    //           \         /
    //              (123)                <--------- to be added
    //
    //--------------------------------------------------------------------------------------------------------------
    $program_entity = $this->createMock(Program::class);
    $program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('123');
    $program_entity->expects($this->atLeastOnce())
      ->method('isInitialVersion')->willReturn(true);
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
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function testAddRemixRelationsForOnlyOneExistingParent(): void
  {
    //--------------------------------------------------------------------------------------------------------------
    //
    //    doesn't exist any more -->            (3570)    (16267)
    //                                                       |
    //                                                     (123)              <--------- to be added
    //
    //--------------------------------------------------------------------------------------------------------------
    $program_entity = $this->createMock(Program::class);
    $program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('123');
    $program_entity->expects($this->atLeastOnce())
      ->method('isInitialVersion')->willReturn(true);
    $program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;

    $first_parent_entity = $this->createMock(Program::class);
    $first_parent_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('3570');
    $first_parent_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;

    $second_parent_entity = $this->createMock(Program::class);
    $second_parent_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('16267');
    $second_parent_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;

    $parent_data = [
      $first_parent_entity->getId() => [
        'isScratch' => false,
        'entity' => $first_parent_entity,
        'exists' => false,
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
      new ProgramRemixRelation($second_parent_entity, $program_entity, 1),
    ];
    $this->checkRemixRelations($program_entity, $parent_data, $expected_relations);
    $this->assertFalse($program_entity->isRemixRoot());
  }

  /**
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function testAddRemixRelationsForExistingParents(): void
  {
    //--------------------------------------------------------------------------------------------------------------
    //
    //                 (3570)    (16267)
    //                     \       /
    //                       (123)              <--------- to be added
    //
    //--------------------------------------------------------------------------------------------------------------
    $program_entity = $this->createMock(Program::class);
    $program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('123');
    $program_entity->expects($this->atLeastOnce())
      ->method('isInitialVersion')->willReturn(true);
    $program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;

    $first_parent_entity = $this->createMock(Program::class);
    $first_parent_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('3570');
    $first_parent_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $second_parent_entity = $this->createMock(Program::class);
    $second_parent_entity->expects($this->atLeastOnce())
      ->method('getId')
      ->willReturn('16267')
    ;
    $second_parent_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;

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
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function testAddRemixRelationsForExistingParentsSharingSameParent(): void
  {
    //--------------------------------------------------------------------------------------------------------------
    //                       (1)
    //                     /     \
    //                    (2)   (3)
    //                     \     /
    //                       (4)              <--------- to be added
    //
    //--------------------------------------------------------------------------------------------------------------
    $parent_entity_of_both_parents = $this->createMock(Program::class);
    $parent_entity_of_both_parents->expects($this->atLeastOnce())
      ->method('getId')->willReturn('1');
    $parent_entity_of_both_parents->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $first_parent_entity = $this->createMock(Program::class);
    $first_parent_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('2');
    $first_parent_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $second_parent_entity = $this->createMock(Program::class);
    $second_parent_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('3');
    $second_parent_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $program_entity = $this->createMock(Program::class);
    $program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('4');
    $program_entity->expects($this->atLeastOnce())
      ->method('isInitialVersion')->willReturn(true);
    $program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
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
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function testAddRemixRelationsForExistingParentsHavingDifferentParent(): void
  {
    //--------------------------------------------------------------------------------------------------------------
    //                    (1)    (2)
    //                     |      |
    //                    (3)    (4)
    //                      \     /
    //                        (5)              <--------- to be added
    //
    //--------------------------------------------------------------------------------------------------------------
    $parent_entity_of_first_parent = $this->createMock(Program::class);
    $parent_entity_of_first_parent->expects($this->atLeastOnce())
      ->method('getId')->willReturn('1');
    $parent_entity_of_first_parent->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $parent_entity_of_second_parent = $this->createMock(Program::class);
    $parent_entity_of_second_parent->expects($this->atLeastOnce())
      ->method('getId')->willReturn('2');
    $parent_entity_of_second_parent->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $first_parent_entity = $this->createMock(Program::class);
    $first_parent_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('3');
    $first_parent_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $second_parent_entity = $this->createMock(Program::class);
    $second_parent_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('4');
    $second_parent_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $program_entity = $this->createMock(Program::class);
    $program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('5');
    $program_entity->expects($this->atLeastOnce())
      ->method('isInitialVersion')->willReturn(true);
    $program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
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
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function testAddRemixRelationsForScratchParent(): void
  {
    //--------------------------------------------------------------------------------------------------------------
    //                    (1) (SCRATCH)
    //                     |      |   \
    //                    (2)    (3)  |
    //                      \     /   |
    //                        (4) ____/        <--------- to be added
    //
    //--------------------------------------------------------------------------------------------------------------
    $parent_entity_of_first_parent = $this->createMock(Program::class);
    $parent_entity_of_first_parent->expects($this->atLeastOnce())
      ->method('getId')->willReturn('1');
    $parent_entity_of_first_parent->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $scratch_parent_id = '29495624';
    $first_parent_entity = $this->createMock(Program::class);
    $first_parent_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('2');
    $first_parent_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $second_parent_entity = $this->createMock(Program::class);
    $second_parent_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('3');
    $second_parent_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $program_entity = $this->createMock(Program::class);
    $program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('4');
    $program_entity->expects($this->atLeastOnce())
      ->method('isInitialVersion')->willReturn(true);
    $program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
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
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function testAddRemixRelationsForMoreComplexGraph1(): void
  {
    //--------------------------------------------------------------------------------------------------------------
    //
    //                (1)      (2)
    //                   \   /     \
    //                    \ /       \
    //                    (3)      (4)
    //                       \     /
    //                        \   /
    //                         (5)             <--------- to be added
    //
    //--------------------------------------------------------------------------------------------------------------
    $first_program_entity = $this->createMock(Program::class);
    $first_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('1');
    $first_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $second_program_entity = $this->createMock(Program::class);
    $second_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('2');
    $second_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $third_program_entity = $this->createMock(Program::class);
    $third_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('3');
    $third_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $fourth_program_entity = $this->createMock(Program::class);
    $fourth_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('4');
    $fourth_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $program_entity = $this->createMock(Program::class);
    $program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('5');
    $program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $program_entity->expects($this->atLeastOnce())
      ->method('isInitialVersion')->willReturn(true);
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
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function testAddRemixRelationsForMoreComplexGraph2(): void
  {
    //--------------------------------------------------------------------------------------------------------------
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
    //--------------------------------------------------------------------------------------------------------------
    $first_program_entity = $this->createMock(Program::class);
    $first_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('1');
    $first_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $second_program_entity = $this->createMock(Program::class);
    $second_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('2');
    $second_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $third_program_entity = $this->createMock(Program::class);
    $third_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('3');
    $third_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $fourth_program_entity = $this->createMock(Program::class);
    $fourth_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('4');
    $fourth_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $fifth_program_entity = $this->createMock(Program::class);
    $fifth_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('5');
    $fifth_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $sixth_program_entity = $this->createMock(Program::class);
    $sixth_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('6');
    $sixth_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $program_entity = $this->createMock(Program::class);
    $program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('7');
    $program_entity->expects($this->atLeastOnce())
      ->method('isInitialVersion')->willReturn(true);
    $program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
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
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function testAddRemixRelationsForMoreComplexGraph3(): void
  {
    //--------------------------------------------------------------------------------------------------------------
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
    //--------------------------------------------------------------------------------------------------------------
    $first_program_entity = $this->createMock(Program::class);
    $first_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('1');
    $first_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $second_program_entity = $this->createMock(Program::class);
    $second_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('2');
    $second_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $third_program_entity = $this->createMock(Program::class);
    $third_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('3');
    $third_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $fourth_program_entity = $this->createMock(Program::class);
    $fourth_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('4');
    $fourth_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $fifth_program_entity = $this->createMock(Program::class);
    $fifth_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('5');
    $fifth_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $sixth_program_entity = $this->createMock(Program::class);
    $sixth_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('6');
    $sixth_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $program_entity = $this->createMock(Program::class);
    $program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('7');
    $program_entity->expects($this->atLeastOnce())
      ->method('isInitialVersion')->willReturn(true);
    $program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
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
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function testAddRemixRelationsForMoreComplexGraph4(): void
  {
    //--------------------------------------------------------------------------------------------------------------
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
    //--------------------------------------------------------------------------------------------------------------
    $first_program_entity = $this->createMock(Program::class);
    $first_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('1');
    $first_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $second_program_entity = $this->createMock(Program::class);
    $second_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('2');
    $second_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $third_program_entity = $this->createMock(Program::class);
    $third_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('3');
    $third_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $fourth_program_entity = $this->createMock(Program::class);
    $fourth_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('4');
    $fourth_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $fifth_program_entity = $this->createMock(Program::class);
    $fifth_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('5');
    $fifth_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $sixth_program_entity = $this->createMock(Program::class);
    $sixth_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('6');
    $sixth_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $program_entity = $this->createMock(Program::class);
    $program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('7');
    $program_entity->expects($this->atLeastOnce())->method('isInitialVersion')->willReturn(true);
    $program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
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
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function testAddRemixRelationsForMoreComplexGraph5(): void
  {
    //--------------------------------------------------------------------------------------------------------------
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
    //--------------------------------------------------------------------------------------------------------------

    $scratch_parent_id = '29495624';

    $first_program_entity = $this->createMock(Program::class);
    $first_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('1');
    $first_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $second_program_entity = $this->createMock(Program::class);
    $second_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('2');
    $second_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $third_program_entity = $this->createMock(Program::class);
    $third_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('3');
    $third_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $fourth_program_entity = $this->createMock(Program::class);
    $fourth_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('4');
    $fourth_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $fifth_program_entity = $this->createMock(Program::class);
    $fifth_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('5');
    $fifth_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $program_entity = $this->createMock(Program::class);
    $program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('6');
    $program_entity->expects($this->atLeastOnce())
      ->method('isInitialVersion')->willReturn(true);
    $program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;

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
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function testAddRemixRelationsForMoreComplexGraph6(): void
  {
    //--------------------------------------------------------------------------------------------------------------
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
    //--------------------------------------------------------------------------------------------------------------
    $first_scratch_ancestor_id = '124742637';
    $second_scratch_parent_id = '29495624';

    $first_program_entity = $this->createMock(Program::class);
    $first_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('1');
    $first_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $second_program_entity = $this->createMock(Program::class);
    $second_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('2');
    $second_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $third_program_entity = $this->createMock(Program::class);
    $third_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('3');
    $third_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $fourth_program_entity = $this->createMock(Program::class);
    $fourth_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('4');
    $fourth_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $program_entity = $this->createMock(Program::class);
    $program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('5');
    $program_entity->expects($this->atLeastOnce())
      ->method('isInitialVersion')->willReturn(true);
    $program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;

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
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function testAddRemixRelationsForMoreComplexGraph7(): void
  {
    //--------------------------------------------------------------------------------------------------------------
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
    //--------------------------------------------------------------------------------------------------------------

    $first_scratch_ancestor_id = '127781769';
    $second_scratch_parent_id = '29495624';
    $third_scratch_parent_id = '124742637';

    $first_program_entity = $this->createMock(Program::class);
    $first_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('1');
    $first_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $second_program_entity = $this->createMock(Program::class);
    $second_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('2');
    $second_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $third_program_entity = $this->createMock(Program::class);
    $third_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('3');
    $third_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $fourth_program_entity = $this->createMock(Program::class);
    $fourth_program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('4');
    $fourth_program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;
    $program_entity = $this->createMock(Program::class);
    $program_entity->expects($this->atLeastOnce())
      ->method('getId')->willReturn('5');
    $program_entity->expects($this->atLeastOnce())
      ->method('isInitialVersion')->willReturn(true);
    $program_entity->expects($this->any())
      ->method('getUser')
      ->willReturn($this->createMock(User::class))
    ;

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
   * @param mixed $program_entity
   * @param mixed $parent_data
   * @param mixed $expected_relations
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  private function checkRemixRelations($program_entity, $parent_data, $expected_relations): void
  {
    /** @var MockObject|Program $program_entity */
    $expected_relations_map = [];
    $expected_catrobat_relations = [];
    foreach ($expected_relations as $expected_relation)
    {
      if ($expected_relation instanceof ProgramRemixRelation)
      {
        $expected_catrobat_relations[] = $expected_relation;
      }
      $expected_relations_map[$expected_relation->getUniqueKey()] = $expected_relation;
    }

    $program_repository_find_map = [];
    $program_remix_repository_find_map = [];

    foreach ($parent_data as $parent_id => $data)
    {
      $catrobat_relations = array_filter($data['existingRelations'], fn ($relation) => $relation instanceof ProgramRemixRelation);
      $program_remix_repository_find_map[] = [['descendant_id' => (string) $parent_id], null, null, null, $catrobat_relations];
      $program_repository_find_map[] = [(string) $parent_id, null, null, $data['exists'] ? $data['entity'] : null];
    }

    $this->program_repository
      ->expects($this->any())
      ->method('find')
      ->willReturn($this->returnValueMap($program_repository_find_map))
    ;

    $this->program_remix_repository
      ->expects($this->any())
      ->method('findBy')
      ->willReturn($this->returnValueMap($program_remix_repository_find_map))
    ;

    $this->entity_manager
      ->expects($this->atLeastOnce())
      ->method('persist')
      ->will($this->returnCallback(function ($arg) use ($program_entity, &$expected_relations_map)
      {
        if ($arg instanceof ProgramRemixRelation || $arg instanceof ScratchProgramRemixRelation)
        {
          $relation = $arg;
          Assert::assertArrayHasKey($relation->getUniqueKey(), $expected_relations_map);
          unset($expected_relations_map[$relation->getUniqueKey()]);
        }

        if ($arg instanceof Program)
        {
          Assert::assertEquals($arg, $program_entity);
        }
      }))
    ;

    $this->entity_manager->expects($this->atLeastOnce())->method('flush');

    $remixes_data = [];
    foreach ($parent_data as $parent_id => $data)
    {
      $remixes_data[] = new RemixData(!$data['isScratch'] ? '/app/project/'.$parent_id
        : 'https://scratch.mit.edu/projects/'.$parent_id.'/');
    }

    Assert::assertCount(is_countable($expected_relations) ? count($expected_relations) : 0, $expected_relations_map);

    $expected_to_be_root = (1 === count($expected_catrobat_relations));
    $program_entity->expects($this->atLeastOnce())
      ->method('isRemixRoot')->willReturn($expected_to_be_root);
    $this->remix_manager->addRemixes($program_entity, $remixes_data);
    Assert::assertCount(0, $expected_relations_map);
  }
}
