<?php
namespace spec\Catrobat\AppBundle\Entity;

use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Entity\ProgramRemixBackwardRepository;
use Catrobat\AppBundle\Entity\ProgramRemixRelation;
use Catrobat\AppBundle\Entity\ProgramRemixRepository;
use Catrobat\AppBundle\Entity\ProgramRepository;
use Catrobat\AppBundle\Entity\ScratchProgramRemixRelation;
use Catrobat\AppBundle\Entity\ScratchProgramRemixRepository;
use Catrobat\AppBundle\Entity\ScratchProgramRepository;
use Catrobat\AppBundle\RemixGraph\RemixGraphManipulator;
use Catrobat\AppBundle\Services\RemixData;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use \Doctrine\Common\Collections\ArrayCollection;

class RemixManagerSpec extends ObjectBehavior
{

    public function let(EntityManager $entity_manager, ProgramRepository $program_repository, ScratchProgramRepository $scratch_program_repository, ProgramRemixRepository $program_remix_repository,
                        ProgramRemixBackwardRepository $program_remix_backward_repository, ScratchProgramRemixRepository $scratch_program_remix_repository, RemixGraphManipulator $remix_graph_manipulator,
                        Program $program_entity)
    {
        $this->beConstructedWith($entity_manager, $program_repository, $scratch_program_repository,
            $program_remix_repository, $program_remix_backward_repository, $scratch_program_remix_repository,
            $remix_graph_manipulator);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Catrobat\AppBundle\Entity\RemixManager');
    }

    public function it_add_single_scratch_program(EntityManager $entity_manager, ScratchProgramRemixRepository $scratch_program_repository)
    {
        $expected_id_of_first_program = 123;
        $expected_name_of_first_program = 'Test program';
        $expected_description_of_first_program = 'My description';
        $expected_username_of_first_program = 'John Doe';

        $scratch_info_data = [$expected_id_of_first_program => [
            'id' => $expected_id_of_first_program,
            'creator' => ['username' => $expected_username_of_first_program],
            'title' => $expected_name_of_first_program,
            'description' => $expected_description_of_first_program
        ]];

        $scratch_program_repository
            ->find(Argument::exact($expected_id_of_first_program))
            ->shouldBeCalled()
            ->willReturn(null);

        $entity_manager
            ->persist(Argument::type('\Catrobat\AppBundle\Entity\ScratchProgram'))
            ->shouldBeCalled()
            ->will(function ($args) use ($expected_id_of_first_program, $expected_name_of_first_program,
                $expected_description_of_first_program, $expected_username_of_first_program)
            {
                expect($args[0])->shouldBeAnInstanceOf('\Catrobat\AppBundle\Entity\ScratchProgram');
                expect($args[0])->getId()->shouldReturn($expected_id_of_first_program);
                expect($args[0])->getName()->shouldReturn($expected_name_of_first_program);
                expect($args[0])->getDescription()->shouldReturn($expected_description_of_first_program);
                expect($args[0])->getUsername()->shouldReturn($expected_username_of_first_program);
            });

        $entity_manager->flush()->shouldBeCalled();

        $this->addScratchPrograms($scratch_info_data);
    }

    public function it_add_single_scratch_program_with_missing_data(EntityManager $entity_manager, ScratchProgramRemixRepository $scratch_program_repository)
    {
        $expected_id_of_first_program = 123;
        $scratch_info_data = [$expected_id_of_first_program => []];

        $scratch_program_repository
            ->find(Argument::exact($expected_id_of_first_program))
            ->shouldBeCalled()
            ->willReturn(null);

        $entity_manager
            ->persist(Argument::type('\Catrobat\AppBundle\Entity\ScratchProgram'))
            ->shouldBeCalled()
            ->will(function ($args) use ($expected_id_of_first_program)
            {
                expect($args[0])->shouldBeAnInstanceOf('\Catrobat\AppBundle\Entity\ScratchProgram');
                expect($args[0])->getId()->shouldReturn($expected_id_of_first_program);
                expect($args[0])->getName()->shouldReturn(null);
                expect($args[0])->getDescription()->shouldReturn(null);
                expect($args[0])->getUsername()->shouldReturn(null);
            });

        $entity_manager->flush()->shouldBeCalled();

        $this->addScratchPrograms($scratch_info_data);
    }

    public function it_add_multiple_scratch_programs(EntityManager $entity_manager, ScratchProgramRemixRepository $scratch_program_repository)
    {
        $expected_id_of_first_program = 123;
        $expected_name_of_first_program = 'Test program';
        $expected_description_of_first_program = 'My description';
        $expected_username_of_first_program = 'John Doe';

        $expected_id_of_second_program = 121;
        $expected_name_of_second_program = 'Other test program';
        $expected_username_of_second_program = 'Chuck Norris';

        $scratch_info_data = [
            $expected_id_of_first_program => [
                'id' => $expected_id_of_first_program,
                'creator' => ['username' => $expected_username_of_first_program],
                'title' => $expected_name_of_first_program,
                'description' => $expected_description_of_first_program
            ], $expected_id_of_second_program => [
                'id' => $expected_id_of_second_program,
                'creator' => ['username' => $expected_username_of_second_program],
                'title' => $expected_name_of_second_program
            ]
        ];

        $scratch_program_repository
            ->find(Argument::exact($expected_id_of_first_program))
            ->shouldBeCalled()
            ->willReturn(null);

        $scratch_program_repository
            ->find(Argument::exact($expected_id_of_second_program))
            ->shouldBeCalled()
            ->willReturn(null);

        $entity_manager
            ->persist(Argument::type('\Catrobat\AppBundle\Entity\ScratchProgram'))
            ->shouldBeCalled()
            ->will(function ($args) use ($expected_id_of_first_program, $expected_name_of_first_program,
                $expected_description_of_first_program, $expected_username_of_first_program,
                $expected_id_of_second_program, $expected_name_of_second_program, $expected_username_of_second_program)
            {
                expect($args[0])->shouldBeAnInstanceOf('\Catrobat\AppBundle\Entity\ScratchProgram');
                if ($args[0]->getId() == $expected_id_of_first_program) {
                    expect($args[0])->getName()->shouldReturn($expected_name_of_first_program);
                    expect($args[0])->getDescription()->shouldReturn($expected_description_of_first_program);
                    expect($args[0])->getUsername()->shouldReturn($expected_username_of_first_program);
                } else if ($args[0]->getId() == $expected_id_of_second_program) {
                    expect($args[0])->getName()->shouldReturn($expected_name_of_second_program);
                    expect($args[0])->getDescription()->shouldReturn(null);
                    expect($args[0])->getUsername()->shouldReturn($expected_username_of_second_program);
                } else {

                }
            });

        $entity_manager->flush()->shouldBeCalled();

        $this->addScratchPrograms($scratch_info_data);
    }

    public function testRemixRelations(Program $program_entity, $parent_data, $expected_relations, ProgramRepository $program_repository,
                                       ProgramRemixRepository $program_remix_repository, EntityManager $entity_manager)
    {
        $expected_relations_map = [];
        $expected_catrobat_relations = [];
        foreach ($expected_relations as $expected_relation) {
            if ($expected_relation instanceof ProgramRemixRelation) {
                $expected_catrobat_relations[] = $expected_relation;
            }
            $expected_relations_map[$expected_relation->getUniqueKey()] = $expected_relation;
        }

        $has_scratch_relations = false;

        foreach ($parent_data as $parent_id => $data) {
            if (!$data['isScratch']) {
                $program_repository
                    ->find(Argument::exact($parent_id))
                    ->willReturn($data['exists'] ? $data['entity'] : null);

                $catrobat_relations = array_filter($data['existingRelations'], function($r) { return $r instanceof ProgramRemixRelation; });

                $program_remix_repository
                    ->findBy(Argument::exact(array('descendant_id' => $parent_id)))
                    ->willReturn($catrobat_relations);
            } else {
                $has_scratch_relations = true;
            }
        }

        $entity_manager
            ->persist(Argument::type('\Catrobat\AppBundle\Entity\Program'))
            ->shouldBeCalled()
            ->will(function ($args) use ($program_entity) { expect($args[0])->shouldBeEqualTo($program_entity); });

        $spec_this = $this;

        $entity_manager
            ->persist(Argument::type('\Catrobat\AppBundle\Entity\ProgramRemixRelation'))
            ->shouldBeCalled()
            ->will(function ($args) use ($spec_this, &$expected_relations_map) {
                expect($expected_relations_map)->shouldHaveKey($args[0]->getUniqueKey());
                unset($expected_relations_map[$args[0]->getUniqueKey()]);
            });

        if ($has_scratch_relations) {
            $entity_manager
                ->persist(Argument::type('\Catrobat\AppBundle\Entity\ScratchProgramRemixRelation'))
                ->shouldBeCalled()
                ->will(function ($args) use ($spec_this, &$expected_relations_map) {
                    expect($expected_relations_map)->shouldHaveKey($args[0]->getUniqueKey());
                    unset($expected_relations_map[$args[0]->getUniqueKey()]);
                });
        }

        $entity_manager->flush()->shouldBeCalled();

        $remixes_data = array();
        foreach ($parent_data as $parent_id => $data) {
            $remixes_data[] = new RemixData(!$data['isScratch'] ? '/pocketcode/program/' . $parent_id
                                                                : 'https://scratch.mit.edu/projects/' . $parent_id . '/');
        }

        expect($expected_relations_map)->shouldHaveCount(count($expected_relations));
        $this->addRemixes($program_entity, $remixes_data);
        $expected_to_be_root = (count($expected_catrobat_relations) == 1);
        expect($program_entity)->isRemixRoot()->shouldReturn($expected_to_be_root);
        expect($expected_relations_map)->shouldHaveCount(0);
    }

    public function it_set_program_as_root_and_dont_add_remix_relations_when_no_parents_are_given(
        ProgramRepository $program_repository, ProgramRemixRepository $program_remix_repository, EntityManager $entity_manager)
    {
        $program_entity = new Program();
        $program_entity->setId(123);
        $program_entity->setVersion(Program::INITIAL_VERSION);

        $parent_data = array();

        $expected_relations = array(
            new ProgramRemixRelation($program_entity, $program_entity, 0)
        );

        $this->testRemixRelations($program_entity, $parent_data, $expected_relations, $program_repository,
            $program_remix_repository, $entity_manager);
        expect($program_entity)->isRemixRoot()->shouldReturn(true);
    }

    public function it_set_program_as_root_and_dont_add_remix_relations_for_non_existing_parents(
        ProgramRepository $program_repository, ProgramRemixRepository $program_remix_repository, EntityManager $entity_manager)
    {
        $program_entity = new Program();
        $program_entity->setId(123);
        $program_entity->setVersion(Program::INITIAL_VERSION);

        $first_parent_entity = new Program();
        $first_parent_entity->setId(3570);

        $second_parent_entity = new Program();
        $second_parent_entity->setId(16267);

        $parent_data = array(
            $first_parent_entity->getId()  => array(
                'isScratch' => false,
                'entity' => $first_parent_entity,
                'exists' => false,
                'existingRelations' => array()
            ),
            $second_parent_entity->getId() => array(
                'isScratch' => false,
                'entity' => $second_parent_entity,
                'exists' => false,
                'existingRelations' => array()
            )
        );

        $expected_relations = array(
            new ProgramRemixRelation($program_entity, $program_entity, 0)
        );

        $this->testRemixRelations($program_entity, $parent_data, $expected_relations, $program_repository,
            $program_remix_repository, $entity_manager);
        expect($program_entity)->isRemixRoot()->shouldReturn(true);
    }

    public function it_set_program_as_root_if_only_has_scratch_parents(ProgramRepository $program_repository, ProgramRemixRepository $program_remix_repository,
                                                                       EntityManager $entity_manager)
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

        $program_entity = new Program();
        $program_entity->setId(123);
        $program_entity->setVersion(Program::INITIAL_VERSION);

        $first_scratch_parent_id = 1;
        $second_scratch_parent_id = 2;

        $parent_data = array(
            $first_scratch_parent_id => array(
                'isScratch' => true,
                'entity' => null,
                'exists' => true,
                'existingRelations' => array()
            ),
            $second_scratch_parent_id => array(
                'isScratch' => true,
                'entity' => null,
                'exists' => true,
                'existingRelations' => array()
            )
        );

        $expected_relations = array(
            new ProgramRemixRelation($program_entity, $program_entity, 0),
            new ScratchProgramRemixRelation($first_scratch_parent_id, $program_entity),
            new ScratchProgramRemixRelation($second_scratch_parent_id, $program_entity)
        );

        $this->testRemixRelations($program_entity, $parent_data, $expected_relations, $program_repository,
            $program_remix_repository, $entity_manager);
        expect($program_entity)->isRemixRoot()->shouldReturn(true);
    }

    public function it_add_remix_relations_for_only_one_existing_parent(ProgramRepository $program_repository, ProgramRemixRepository $program_remix_repository,
                                                                        EntityManager $entity_manager)
    {
        //--------------------------------------------------------------------------------------------------------------
        //
        //    doesn't exist any more -->            (3570)    (16267)
        //                                                       |
        //                                                     (123)              <--------- to be added
        //
        //--------------------------------------------------------------------------------------------------------------

        $program_entity = new Program();
        $program_entity->setId(123);
        $program_entity->setVersion(Program::INITIAL_VERSION);

        $first_parent_entity = new Program();
        $first_parent_entity->setId(3570);

        $second_parent_entity = new Program();
        $second_parent_entity->setId(16267);

        $parent_data = array(
            $first_parent_entity->getId()  => array(
                'isScratch' => false,
                'entity' => $first_parent_entity,
                'exists' => false,
                'existingRelations' => array()
            ),
            $second_parent_entity->getId() => array(
                'isScratch' => false,
                'entity' => $second_parent_entity,
                'exists' => true,
                'existingRelations' => array()
            )
        );

        $expected_relations = array(
            new ProgramRemixRelation($program_entity, $program_entity, 0),
            new ProgramRemixRelation($second_parent_entity, $program_entity, 1)
        );

        $this->testRemixRelations($program_entity, $parent_data, $expected_relations, $program_repository,
            $program_remix_repository, $entity_manager);
        expect($program_entity)->isRemixRoot()->shouldReturn(false);
    }

    public function it_add_remix_relations_for_existing_parents(ProgramRepository $program_repository, ProgramRemixRepository $program_remix_repository,
                                                                EntityManager $entity_manager)
    {
        //--------------------------------------------------------------------------------------------------------------
        //
        //                 (3570)    (16267)
        //                     \       /
        //                       (123)              <--------- to be added
        //
        //--------------------------------------------------------------------------------------------------------------

        $program_entity = new Program();
        $program_entity->setId(123);
        $program_entity->setVersion(Program::INITIAL_VERSION);

        $first_parent_entity = new Program();
        $first_parent_entity->setId(3570);

        $second_parent_entity = new Program();
        $second_parent_entity->setId(16267);

        $parent_data = array(
            $first_parent_entity->getId()  => array(
                'isScratch' => false,
                'entity' => $first_parent_entity,
                'exists' => true,
                'existingRelations' => array()
            ),
            $second_parent_entity->getId() => array(
                'isScratch' => false,
                'entity' => $second_parent_entity,
                'exists' => true,
                'existingRelations' => array()
            )
        );

        $expected_relations = array(
            new ProgramRemixRelation($program_entity, $program_entity, 0),
            new ProgramRemixRelation($first_parent_entity, $program_entity, 1),
            new ProgramRemixRelation($second_parent_entity, $program_entity, 1)
        );

        $this->testRemixRelations($program_entity, $parent_data, $expected_relations, $program_repository,
            $program_remix_repository, $entity_manager);
        expect($program_entity)->isRemixRoot()->shouldReturn(false);
    }

    public function it_add_remix_relations_for_existing_parents_sharing_same_parent(ProgramRepository $program_repository, ProgramRemixRepository $program_remix_repository,
                                                                                    EntityManager $entity_manager)
    {
        //--------------------------------------------------------------------------------------------------------------
        //                       (1)
        //                     /     \
        //                    (2)   (3)
        //                     \     /
        //                       (4)              <--------- to be added
        //
        //--------------------------------------------------------------------------------------------------------------

        $parent_entity_of_both_parents = new Program();
        $parent_entity_of_both_parents->setId(1);

        $first_parent_entity = new Program();
        $first_parent_entity->setId(2);

        $second_parent_entity = new Program();
        $second_parent_entity->setId(3);

        $program_entity = new Program();
        $program_entity->setId(4);
        $program_entity->setVersion(Program::INITIAL_VERSION);

        $parent_data = array(
            $first_parent_entity->getId()  => array(
                'isScratch' => false,
                'entity' => $first_parent_entity,
                'exists' => true,
                'existingRelations' => array(
                    new ProgramRemixRelation($first_parent_entity, $first_parent_entity, 0),
                    new ProgramRemixRelation($parent_entity_of_both_parents, $first_parent_entity, 1)
                )
            ),
            $second_parent_entity->getId() => array(
                'isScratch' => false,
                'entity' => $second_parent_entity,
                'exists' => true,
                'existingRelations' => array(
                    new ProgramRemixRelation($second_parent_entity, $second_parent_entity, 0),
                    new ProgramRemixRelation($parent_entity_of_both_parents, $second_parent_entity, 1)
                )
            )
        );

        $expected_relations = array(
            // self-relation
            new ProgramRemixRelation($program_entity, $program_entity, 0),

            // relation to parents
            new ProgramRemixRelation($first_parent_entity, $program_entity, 1),
            new ProgramRemixRelation($second_parent_entity, $program_entity, 1),

            // relation to grandparents
            new ProgramRemixRelation($parent_entity_of_both_parents, $program_entity, 2)
        );

        $this->testRemixRelations($program_entity, $parent_data, $expected_relations, $program_repository,
            $program_remix_repository, $entity_manager);
        expect($program_entity)->isRemixRoot()->shouldReturn(false);
    }

    public function it_add_remix_relations_for_existing_parents_having_different_parent(ProgramRepository $program_repository, ProgramRemixRepository $program_remix_repository, EntityManager $entity_manager)
    {
        //--------------------------------------------------------------------------------------------------------------
        //                    (1)    (2)
        //                     |      |
        //                    (3)    (4)
        //                      \     /
        //                        (5)              <--------- to be added
        //
        //--------------------------------------------------------------------------------------------------------------

        $parent_entity_of_first_parent = new Program();
        $parent_entity_of_first_parent->setId(1);

        $parent_entity_of_second_parent = new Program();
        $parent_entity_of_second_parent->setId(2);

        $first_parent_entity = new Program();
        $first_parent_entity->setId(3);

        $second_parent_entity = new Program();
        $second_parent_entity->setId(4);

        $program_entity = new Program();
        $program_entity->setId(5);
        $program_entity->setVersion(Program::INITIAL_VERSION);

        $parent_data = array(
            $first_parent_entity->getId()  => array(
                'isScratch' => false,
                'entity' => $first_parent_entity,
                'exists' => true,
                'existingRelations' => array(
                    new ProgramRemixRelation($first_parent_entity, $first_parent_entity, 0),
                    new ProgramRemixRelation($parent_entity_of_first_parent, $first_parent_entity, 1)
                )
            ),
            $second_parent_entity->getId() => array(
                'isScratch' => false,
                'entity' => $second_parent_entity,
                'exists' => true,
                'existingRelations' => array(
                    new ProgramRemixRelation($second_parent_entity, $second_parent_entity, 0),
                    new ProgramRemixRelation($parent_entity_of_second_parent, $second_parent_entity, 1)
                )
            )
        );

        $expected_relations = array(
            // self-relation
            new ProgramRemixRelation($program_entity, $program_entity, 0),

            // relation to parents
            new ProgramRemixRelation($first_parent_entity, $program_entity, 1),
            new ProgramRemixRelation($second_parent_entity, $program_entity, 1),

            // relation to grandparents
            new ProgramRemixRelation($parent_entity_of_first_parent, $program_entity, 2),
            new ProgramRemixRelation($parent_entity_of_second_parent, $program_entity, 2)
        );

        $this->testRemixRelations($program_entity, $parent_data, $expected_relations, $program_repository,
            $program_remix_repository, $entity_manager);
        expect($program_entity)->isRemixRoot()->shouldReturn(false);
    }

    public function it_add_remix_relations_for_scratch_parent(ProgramRepository $program_repository, ProgramRemixRepository $program_remix_repository, EntityManager $entity_manager)
    {
        //--------------------------------------------------------------------------------------------------------------
        //                    (1) (SCRATCH)
        //                     |      |   \
        //                    (2)    (3)  |
        //                      \     /   |
        //                        (4) ____/        <--------- to be added
        //
        //--------------------------------------------------------------------------------------------------------------

        $parent_entity_of_first_parent = new Program();
        $parent_entity_of_first_parent->setId(1);

        $scratch_parent_id = 29495624;

        $first_parent_entity = new Program();
        $first_parent_entity->setId(2);

        $second_parent_entity = new Program();
        $second_parent_entity->setId(3);

        $program_entity = new Program();
        $program_entity->setId(4);
        $program_entity->setVersion(Program::INITIAL_VERSION);

        $parent_data = array(
            $first_parent_entity->getId()  => array(
                'isScratch' => false,
                'entity' => $first_parent_entity,
                'exists' => true,
                'existingRelations' => array(
                    new ProgramRemixRelation($first_parent_entity, $first_parent_entity, 0),
                    new ProgramRemixRelation($parent_entity_of_first_parent, $first_parent_entity, 1)
                )
            ),
            $second_parent_entity->getId() => array(
                'isScratch' => false,
                'entity' => $second_parent_entity,
                'exists' => true,
                'existingRelations' => array(
                    new ProgramRemixRelation($second_parent_entity, $second_parent_entity, 0),
                    new ScratchProgramRemixRelation($scratch_parent_id, $second_parent_entity, 1)
                )
            ),
            $scratch_parent_id => array(
                'isScratch' => true,
                'entity' => null,
                'exists' => true,
                'existingRelations' => array()
            )
        );

        $expected_relations = array(
            // self-relation
            new ProgramRemixRelation($program_entity, $program_entity, 0),

            // relation to parents
            new ProgramRemixRelation($first_parent_entity, $program_entity, 1),
            new ProgramRemixRelation($second_parent_entity, $program_entity, 1),
            new ScratchProgramRemixRelation($scratch_parent_id, $program_entity),

            // relation to grandparents
            new ProgramRemixRelation($parent_entity_of_first_parent, $program_entity, 2),
        );

        $this->testRemixRelations($program_entity, $parent_data, $expected_relations, $program_repository,
            $program_remix_repository, $entity_manager);
        expect($program_entity)->isRemixRoot()->shouldReturn(false);
    }

    public function it_add_remix_relations_for_more_complex_graph_1(ProgramRepository $program_repository, ProgramRemixRepository $program_remix_repository, EntityManager $entity_manager)
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

        $first_program_entity = new Program();
        $first_program_entity->setId(1);

        $second_program_entity = new Program();
        $second_program_entity->setId(2);

        $third_program_entity = new Program();
        $third_program_entity->setId(3);

        $fourth_program_entity = new Program();
        $fourth_program_entity->setId(4);

        $program_entity = new Program();
        $program_entity->setId(5);
        $program_entity->setVersion(Program::INITIAL_VERSION);

        $parent_data = array(
            $third_program_entity->getId()  => array(
                'isScratch' => false,
                'entity' => $third_program_entity,
                'exists' => true,
                'existingRelations' => array(
                    new ProgramRemixRelation($third_program_entity, $third_program_entity, 0),
                    new ProgramRemixRelation($first_program_entity, $third_program_entity, 1),
                    new ProgramRemixRelation($second_program_entity, $third_program_entity, 1)
                )
            ),
            $fourth_program_entity->getId() => array(
                'isScratch' => false,
                'entity' => $fourth_program_entity,
                'exists' => true,
                'existingRelations' => array(
                    new ProgramRemixRelation($fourth_program_entity, $fourth_program_entity, 0),
                    new ProgramRemixRelation($second_program_entity, $fourth_program_entity, 1)
                )
            )
        );

        $expected_relations = array(
            // self-relation
            new ProgramRemixRelation($program_entity, $program_entity, 0),

            // relation to parents
            new ProgramRemixRelation($third_program_entity, $program_entity, 1),
            new ProgramRemixRelation($fourth_program_entity, $program_entity, 1),

            // relation to grandparents
            new ProgramRemixRelation($first_program_entity, $program_entity, 2),
            new ProgramRemixRelation($second_program_entity, $program_entity, 2)
        );

        $this->testRemixRelations($program_entity, $parent_data, $expected_relations, $program_repository,
            $program_remix_repository, $entity_manager);
        expect($program_entity)->isRemixRoot()->shouldReturn(false);
    }

    public function it_add_remix_relations_for_more_complex_graph_2(ProgramRepository $program_repository, ProgramRemixRepository $program_remix_repository, EntityManager $entity_manager)
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

        $first_program_entity = new Program();
        $first_program_entity->setId(1);

        $second_program_entity = new Program();
        $second_program_entity->setId(2);

        $third_program_entity = new Program();
        $third_program_entity->setId(3);

        $fourth_program_entity = new Program();
        $fourth_program_entity->setId(4);

        $fifth_program_entity = new Program();
        $fifth_program_entity->setId(5);

        $sixth_program_entity = new Program();
        $sixth_program_entity->setId(6);

        $program_entity = new Program();
        $program_entity->setId(7);
        $program_entity->setVersion(Program::INITIAL_VERSION);

        $parent_data = array(
            $fifth_program_entity->getId()  => array(
                'isScratch' => false,
                'entity' => $fifth_program_entity,
                'exists' => true,
                'existingRelations' => array(
                    new ProgramRemixRelation($fifth_program_entity, $fifth_program_entity, 0),
                    new ProgramRemixRelation($third_program_entity, $fifth_program_entity, 1),
                    new ProgramRemixRelation($fourth_program_entity, $fifth_program_entity, 1),
                    new ProgramRemixRelation($first_program_entity, $fifth_program_entity, 2),
                    new ProgramRemixRelation($second_program_entity, $fifth_program_entity, 2)
                )
            ),
            $sixth_program_entity->getId() => array(
                'isScratch' => false,
                'entity' => $sixth_program_entity,
                'exists' => true,
                'existingRelations' => array(
                    new ProgramRemixRelation($sixth_program_entity, $sixth_program_entity, 0),
                    new ProgramRemixRelation($fourth_program_entity, $sixth_program_entity, 1),
                    new ProgramRemixRelation($second_program_entity, $sixth_program_entity, 2),
                )
            )
        );

        $expected_relations = array(
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
            new ProgramRemixRelation($second_program_entity, $program_entity, 3)
        );

        $this->testRemixRelations($program_entity, $parent_data, $expected_relations, $program_repository,
            $program_remix_repository, $entity_manager);
        expect($program_entity)->isRemixRoot()->shouldReturn(false);
    }

    public function it_add_remix_relations_for_more_complex_graph_3(ProgramRepository $program_repository, ProgramRemixRepository $program_remix_repository, EntityManager $entity_manager)
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

        $first_program_entity = new Program();
        $first_program_entity->setId(1);

        $second_program_entity = new Program();
        $second_program_entity->setId(2);

        $third_program_entity = new Program();
        $third_program_entity->setId(3);

        $fourth_program_entity = new Program();
        $fourth_program_entity->setId(4);

        $fifth_program_entity = new Program();
        $fifth_program_entity->setId(5);

        $sixth_program_entity = new Program();
        $sixth_program_entity->setId(6);

        $program_entity = new Program();
        $program_entity->setId(7);
        $program_entity->setVersion(Program::INITIAL_VERSION);

        $parent_data = array(
            $fifth_program_entity->getId()  => array(
                'isScratch' => false,
                'entity' => $fifth_program_entity,
                'exists' => true,
                'existingRelations' => array(
                    new ProgramRemixRelation($fifth_program_entity, $fifth_program_entity, 0),
                    new ProgramRemixRelation($third_program_entity, $fifth_program_entity, 1),
                    new ProgramRemixRelation($second_program_entity, $fifth_program_entity, 1),
                    new ProgramRemixRelation($first_program_entity, $fifth_program_entity, 2),
                    new ProgramRemixRelation($second_program_entity, $fifth_program_entity, 2)
                )
            ),
            $sixth_program_entity->getId() => array(
                'isScratch' => false,
                'entity' => $sixth_program_entity,
                'exists' => true,
                'existingRelations' => array(
                    new ProgramRemixRelation($sixth_program_entity, $sixth_program_entity, 0),
                    new ProgramRemixRelation($fourth_program_entity, $sixth_program_entity, 1),
                    new ProgramRemixRelation($second_program_entity, $sixth_program_entity, 2),
                )
            )
        );

        $expected_relations = array(
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
            new ProgramRemixRelation($second_program_entity, $program_entity, 3)
        );

        $this->testRemixRelations($program_entity, $parent_data, $expected_relations, $program_repository,
            $program_remix_repository, $entity_manager);
        expect($program_entity)->isRemixRoot()->shouldReturn(false);
    }

    public function it_add_remix_relations_for_more_complex_graph_4(ProgramRepository $program_repository, ProgramRemixRepository $program_remix_repository, EntityManager $entity_manager)
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

        $first_program_entity = new Program();
        $first_program_entity->setId(1);

        $second_program_entity = new Program();
        $second_program_entity->setId(2);

        $third_program_entity = new Program();
        $third_program_entity->setId(3);

        $fourth_program_entity = new Program();
        $fourth_program_entity->setId(4);

        $fifth_program_entity = new Program();
        $fifth_program_entity->setId(5);

        $sixth_program_entity = new Program();
        $sixth_program_entity->setId(6);

        $program_entity = new Program();
        $program_entity->setId(7);
        $program_entity->setVersion(Program::INITIAL_VERSION);

        $parent_data = array(
            $third_program_entity->getId()  => array(
                'isScratch' => false,
                'entity' => $third_program_entity,
                'exists' => true,
                'existingRelations' => array(
                    new ProgramRemixRelation($third_program_entity, $third_program_entity, 0),
                    new ProgramRemixRelation($first_program_entity, $third_program_entity, 1),
                    new ProgramRemixRelation($second_program_entity, $third_program_entity, 1)
                )
            ),
            $fifth_program_entity->getId()  => array(
                'isScratch' => false,
                'entity' => $fifth_program_entity,
                'exists' => true,
                'existingRelations' => array(
                    new ProgramRemixRelation($fifth_program_entity, $fifth_program_entity, 0),
                    new ProgramRemixRelation($third_program_entity, $fifth_program_entity, 1),
                    new ProgramRemixRelation($second_program_entity, $fifth_program_entity, 1),
                    new ProgramRemixRelation($fourth_program_entity, $fifth_program_entity, 1),
                    new ProgramRemixRelation($first_program_entity, $fifth_program_entity, 2),
                    new ProgramRemixRelation($second_program_entity, $fifth_program_entity, 2)
                )
            ),
            $sixth_program_entity->getId() => array(
                'isScratch' => false,
                'entity' => $sixth_program_entity,
                'exists' => true,
                'existingRelations' => array(
                    new ProgramRemixRelation($sixth_program_entity, $sixth_program_entity, 0),
                    new ProgramRemixRelation($fourth_program_entity, $sixth_program_entity, 1),
                    new ProgramRemixRelation($second_program_entity, $sixth_program_entity, 1),
                    new ProgramRemixRelation($second_program_entity, $sixth_program_entity, 2),
                )
            )
        );

        $expected_relations = array(
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
            new ProgramRemixRelation($second_program_entity, $program_entity, 3)
        );

        $this->testRemixRelations($program_entity, $parent_data, $expected_relations, $program_repository,
            $program_remix_repository, $entity_manager);
        expect($program_entity)->isRemixRoot()->shouldReturn(false);
    }

    public function it_add_remix_relations_for_more_complex_graph_5(ProgramRepository $program_repository, ProgramRemixRepository $program_remix_repository, EntityManager $entity_manager)
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

        $first_program_entity = new Program();
        $first_program_entity->setId(1);

        $scratch_parent_id = 29495624;

        $second_program_entity = new Program();
        $second_program_entity->setId(2);

        $third_program_entity = new Program();
        $third_program_entity->setId(3);

        $fourth_program_entity = new Program();
        $fourth_program_entity->setId(4);

        $fifth_program_entity = new Program();
        $fifth_program_entity->setId(5);

        $program_entity = new Program();
        $program_entity->setId(6);
        $program_entity->setVersion(Program::INITIAL_VERSION);

        $parent_data = array(
            $second_program_entity->getId()  => array(
                'isScratch' => false,
                'entity' => $second_program_entity,
                'exists' => true,
                'existingRelations' => array(
                    new ProgramRemixRelation($second_program_entity, $second_program_entity, 0),
                    new ProgramRemixRelation($first_program_entity, $second_program_entity, 1),
                    new ScratchProgramRemixRelation($scratch_parent_id, $second_program_entity)
                )
            ),
            $fourth_program_entity->getId()  => array(
                'isScratch' => false,
                'entity' => $fourth_program_entity,
                'exists' => true,
                'existingRelations' => array(
                    new ProgramRemixRelation($fourth_program_entity, $fourth_program_entity, 0),
                    new ProgramRemixRelation($third_program_entity, $fourth_program_entity, 1),
                    new ProgramRemixRelation($second_program_entity, $fourth_program_entity, 1),
                    new ScratchProgramRemixRelation($scratch_parent_id, $fourth_program_entity),
                    new ProgramRemixRelation($first_program_entity, $fourth_program_entity, 2),
                )
            ),
            $fifth_program_entity->getId() => array(
                'isScratch' => false,
                'entity' => $fifth_program_entity,
                'exists' => true,
                'existingRelations' => array(
                    new ProgramRemixRelation($fifth_program_entity, $fifth_program_entity, 0),
                    new ProgramRemixRelation($third_program_entity, $fifth_program_entity, 1),
                    new ScratchProgramRemixRelation($scratch_parent_id, $fifth_program_entity),
                )
            ),
            $scratch_parent_id => array(
                'isScratch' => true,
                'entity' => null,
                'exists' => true,
                'existingRelations' => array()
            )
        );

        $expected_relations = array(
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
            new ProgramRemixRelation($first_program_entity, $program_entity, 3)
        );

        $this->testRemixRelations($program_entity, $parent_data, $expected_relations, $program_repository,
            $program_remix_repository, $entity_manager);
        expect($program_entity)->isRemixRoot()->shouldReturn(false);
    }

    public function it_add_remix_relations_for_more_complex_graph_6(ProgramRepository $program_repository, ProgramRemixRepository $program_remix_repository, EntityManager $entity_manager)
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

        $first_scratch_ancestor_id = 124742637;
        $second_scratch_parent_id = 29495624;

        $first_program_entity = new Program();
        $first_program_entity->setId(1);

        $second_program_entity = new Program();
        $second_program_entity->setId(3);

        $third_program_entity = new Program();
        $third_program_entity->setId(4);

        $fourth_program_entity = new Program();
        $fourth_program_entity->setId(5);

        $program_entity = new Program();
        $program_entity->setId(5);
        $program_entity->setVersion(Program::INITIAL_VERSION);

        $parent_data = array(
            $first_program_entity->getId()  => array(
                'isScratch' => false,
                'entity' => $first_program_entity,
                'exists' => true,
                'existingRelations' => array(
                    new ProgramRemixRelation($first_program_entity, $first_program_entity, 0),
                    new ScratchProgramRemixRelation($first_scratch_ancestor_id, $first_program_entity),
                    new ScratchProgramRemixRelation($second_scratch_parent_id, $first_program_entity)
                )
            ),
            $third_program_entity->getId()  => array(
                'isScratch' => false,
                'entity' => $third_program_entity,
                'exists' => true,
                'existingRelations' => array(
                    new ProgramRemixRelation($third_program_entity, $third_program_entity, 0),
                    new ProgramRemixRelation($second_program_entity, $third_program_entity, 1),
                    new ProgramRemixRelation($first_program_entity, $third_program_entity, 1),
                    new ScratchProgramRemixRelation($second_scratch_parent_id, $third_program_entity)
                )
            ),
            $fourth_program_entity->getId() => array(
                'isScratch' => false,
                'entity' => $fourth_program_entity,
                'exists' => true,
                'existingRelations' => array(
                    new ProgramRemixRelation($fourth_program_entity, $fourth_program_entity, 0),
                    new ProgramRemixRelation($second_program_entity, $fourth_program_entity, 1),
                    new ScratchProgramRemixRelation($second_scratch_parent_id, $fourth_program_entity)
                )
            ),
            $second_scratch_parent_id => array(
                'isScratch' => true,
                'entity' => null,
                'exists' => true,
                'existingRelations' => array()
            )
        );

        $expected_relations = array(
            // self-relation
            new ProgramRemixRelation($program_entity, $program_entity, 0),

            // relation to parents
            new ProgramRemixRelation($first_program_entity, $program_entity, 1),
            new ProgramRemixRelation($third_program_entity, $program_entity, 1),
            new ProgramRemixRelation($fourth_program_entity, $program_entity, 1),
            new ScratchProgramRemixRelation($second_scratch_parent_id, $program_entity),

            // relation to grandparents
            new ProgramRemixRelation($first_program_entity, $program_entity, 2),
            new ProgramRemixRelation($second_program_entity, $program_entity, 2)
        );

        $this->testRemixRelations($program_entity, $parent_data, $expected_relations, $program_repository,
            $program_remix_repository, $entity_manager);
        expect($program_entity)->isRemixRoot()->shouldReturn(false);
    }

    public function it_add_remix_relations_for_more_complex_graph_7(ProgramRepository $program_repository, ProgramRemixRepository $program_remix_repository, EntityManager $entity_manager)
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

        $first_scratch_ancestor_id = 127781769;
        $second_scratch_parent_id = 29495624;
        $third_scratch_parent_id = 124742637;

        $first_program_entity = new Program();
        $first_program_entity->setId(1);

        $second_program_entity = new Program();
        $second_program_entity->setId(3);

        $third_program_entity = new Program();
        $third_program_entity->setId(4);

        $fourth_program_entity = new Program();
        $fourth_program_entity->setId(5);

        $program_entity = new Program();
        $program_entity->setId(5);
        $program_entity->setVersion(Program::INITIAL_VERSION);

        $parent_data = array(
            $first_program_entity->getId()  => array(
                'isScratch' => false,
                'entity' => $first_program_entity,
                'exists' => true,
                'existingRelations' => array(
                    new ProgramRemixRelation($first_program_entity, $first_program_entity, 0),
                    new ScratchProgramRemixRelation($first_scratch_ancestor_id, $first_program_entity),
                    new ScratchProgramRemixRelation($second_scratch_parent_id, $first_program_entity)
                )
            ),
            $third_program_entity->getId()  => array(
                'isScratch' => false,
                'entity' => $third_program_entity,
                'exists' => true,
                'existingRelations' => array(
                    new ProgramRemixRelation($third_program_entity, $third_program_entity, 0),
                    new ProgramRemixRelation($second_program_entity, $third_program_entity, 1),
                    new ProgramRemixRelation($first_program_entity, $third_program_entity, 1),
                    new ScratchProgramRemixRelation($second_scratch_parent_id, $third_program_entity)
                )
            ),
            $fourth_program_entity->getId() => array(
                'isScratch' => false,
                'entity' => $fourth_program_entity,
                'exists' => true,
                'existingRelations' => array(
                    new ProgramRemixRelation($fourth_program_entity, $fourth_program_entity, 0),
                    new ProgramRemixRelation($second_program_entity, $fourth_program_entity, 1),
                    new ScratchProgramRemixRelation($second_scratch_parent_id, $fourth_program_entity)
                )
            ),
            $second_scratch_parent_id => array(
                'isScratch' => true,
                'entity' => null,
                'exists' => true,
                'existingRelations' => array()
            ),
            $third_scratch_parent_id => array(
                'isScratch' => true,
                'entity' => null,
                'exists' => true,
                'existingRelations' => array()
            )
        );

        $expected_relations = array(
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
            new ProgramRemixRelation($second_program_entity, $program_entity, 2)
        );

        $this->testRemixRelations($program_entity, $parent_data, $expected_relations, $program_repository,
            $program_remix_repository, $entity_manager);
        expect($program_entity)->isRemixRoot()->shouldReturn(false);
    }

    /*
    public function it_update_remix_relations_of_program_after_removing_relation_to_parent($program_repository, $program_remix_repository,
                                                                                           $scratch_program_remix_repository, $entity_manager)
    {
        //--------------------------------------------------------------------------------------------------------------
        //
        //                 (3570)    (16267)                      (16267)
        //                     \       /           ---->             |
        //                       (123)                             (123)
        //
        //--------------------------------------------------------------------------------------------------------------

        $program_entity = new Program();
        $program_entity->setId(123);
        $program_entity->setVersion(2);

        $expected_parent_id_to_be_removed = 3570;
        $first_parent_entity = new Program();
        $first_parent_entity->setId($expected_parent_id_to_be_removed);
        $first_parent_entity->setRemixRoot(true);

        $second_parent_id = 16267;
        $second_parent_entity = new Program();
        $second_parent_entity->setId($second_parent_id);
        $second_parent_entity->setRemixRoot(true);

        $program_entity->setRemixRoot(false);

        $self_relation = new ProgramRemixRelation($program_entity, $program_entity, 0);
        $first_parent_relation = new ProgramRemixRelation($first_parent_entity, $program_entity, 1);
        $second_parent_relation = new ProgramRemixRelation($second_parent_entity, $program_entity, 1);

        $ancestor_relations_property = new \ReflectionProperty(get_class($program_entity), 'catrobat_remix_ancestor_relations');
        $ancestor_relations_property->setAccessible(true);
        $ancestor_relations_property->setValue($program_entity,
            new ArrayCollection(array($self_relation, $first_parent_relation, $second_parent_relation)));

        $descendants_relations_property = new \ReflectionProperty(get_class($program_entity), 'catrobat_remix_descendant_relations');
        $descendants_relations_property->setAccessible(true);
        $descendants_relations_property->setValue($program_entity, new ArrayCollection(array($self_relation)));

        $program_remix_repository
            ->unlinkProgramFromImmediateParents(Argument::exact($program_entity), Argument::exact(array($expected_parent_id_to_be_removed)))
            ->shouldBeCalled()
            ->will(function ($args) use ($ancestor_relations_property, $program_entity) {
                $ancestor_relations = $program_entity->getCatrobatRemixAncestorRelations();
                $ancestor_relations = array_filter($ancestor_relations->getValues(), function ($r) use ($args) { return $r->getAncestorId() != $args[1][0]; });
                $ancestor_relations_property->setValue($program_entity, new ArrayCollection($ancestor_relations));
            });

        $this->addRemixes($program_entity, array(new RemixData('/pocketcode/program/' . $second_parent_id)));

        expect($program_entity)->isRemixRoot()->shouldReturn(false);
        $ancestor_relations = expect($program_entity)->getCatrobatRemixAncestorRelations()->getValues();
        $ancestor_relations->shouldHaveCount(2);
        $ancestor_relations->shouldContain($self_relation);
        $ancestor_relations->shouldNotContain($first_parent_relation);
        $ancestor_relations->shouldContain($second_parent_relation);

        $descendant_relations = expect($program_entity)->getCatrobatRemixDescendantRelations()->getValues();
        $descendant_relations->shouldHaveCount(1);
        $descendant_relations->shouldContain($self_relation);
    }

    public function it_update_remix_relations_of_program_after_removing_relation_to_parent_and_adding_relation_to_new_parent(
        $program_repository, $program_remix_repository, $scratch_program_remix_repository, $entity_manager)
    {
        //--------------------------------------------------------------------------------------------------------------
        //
        //                 (1)   (2)                      (2)   (4)
        //                   \   /           ---->          \   /
        //                    (3)                            (3)
        //
        //--------------------------------------------------------------------------------------------------------------

        $program_entity = new Program();
        $program_entity->setId(3);
        $program_entity->setVersion(2);

        $expected_parent_id_to_be_removed = 1;
        $first_parent_entity = new Program();
        $first_parent_entity->setId($expected_parent_id_to_be_removed);
        $first_parent_entity->setRemixRoot(true);

        $second_parent_id = 2;
        $second_parent_entity = new Program();
        $second_parent_entity->setId($second_parent_id);
        $second_parent_entity->setRemixRoot(true);

        $expected_parent_id_to_be_added = 4;
        $fourth_parent_entity = new Program();
        $fourth_parent_entity->setId($expected_parent_id_to_be_added);
        $fourth_parent_entity->setRemixRoot(true);

        $program_entity->setRemixRoot(false);

        $self_relation = new ProgramRemixRelation($program_entity, $program_entity, 0);
        $first_parent_relation = new ProgramRemixRelation($first_parent_entity, $program_entity, 1);
        $second_parent_relation = new ProgramRemixRelation($second_parent_entity, $program_entity, 1);
        $fourth_parent_relation = new ProgramRemixRelation($fourth_parent_entity, $program_entity, 1);

        $ancestor_relations_property = new \ReflectionProperty(get_class($program_entity), 'catrobat_remix_ancestor_relations');
        $ancestor_relations_property->setAccessible(true);
        $ancestor_relations_property->setValue($program_entity,
            new ArrayCollection(array($self_relation, $first_parent_relation, $second_parent_relation)));

        $descendants_relations_property = new \ReflectionProperty(get_class($program_entity), 'catrobat_remix_descendant_relations');
        $descendants_relations_property->setAccessible(true);
        $descendants_relations_property->setValue($program_entity, new ArrayCollection(array($self_relation)));

        $program_remix_repository
            ->unlinkProgramFromImmediateParents(Argument::exact($program_entity), Argument::exact(array($expected_parent_id_to_be_removed)))
            ->shouldBeCalled()
            ->will(function ($args) use ($ancestor_relations_property, $program_entity) {
                $ancestor_relations = $program_entity->getCatrobatRemixAncestorRelations();
                $ancestor_relations = array_filter($ancestor_relations->getValues(), function ($r) use ($args) { return $r->getAncestorId() != $args[1][0]; });
                $ancestor_relations_property->setValue($program_entity, new ArrayCollection($ancestor_relations));
            });

        $program_remix_repository
            ->appendRemixSubgraphToImmediateParents(Argument::exact($program_entity), Argument::exact(array($expected_parent_id_to_be_added)))
            ->shouldBeCalled()
            ->will(function ($args) use ($ancestor_relations_property, $program_entity, $fourth_parent_relation) {
                $ancestor_relations = $program_entity->getCatrobatRemixAncestorRelations();
                $ancestor_relations->add($fourth_parent_relation);
                $ancestor_relations_property->setValue($program_entity, $ancestor_relations);
            });

        $this->addRemixes($program_entity, array(
            new RemixData('/pocketcode/program/' . $second_parent_id),
            new RemixData('/pocketcode/program/' . $expected_parent_id_to_be_added)
        ));

        expect($program_entity)->isRemixRoot()->shouldReturn(false);
        $ancestor_relations = expect($program_entity)->getCatrobatRemixAncestorRelations()->getValues();
        $ancestor_relations->shouldHaveCount(3);
        $ancestor_relations->shouldContain($self_relation);
        $ancestor_relations->shouldNotContain($first_parent_relation);
        $ancestor_relations->shouldContain($second_parent_relation);
        $ancestor_relations->shouldContain($fourth_parent_relation);

        $descendant_relations = expect($program_entity)->getCatrobatRemixDescendantRelations()->getValues();
        $descendant_relations->shouldHaveCount(1);
        $descendant_relations->shouldContain($self_relation);
    }
    */
}
