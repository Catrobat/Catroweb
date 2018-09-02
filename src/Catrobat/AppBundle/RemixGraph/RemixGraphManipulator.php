<?php

namespace Catrobat\AppBundle\RemixGraph;

use Doctrine\ORM\EntityManager;
use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Entity\ScratchProgramRemixRelation;
use Catrobat\AppBundle\Entity\ProgramRemixRepository;
use Catrobat\AppBundle\Entity\ProgramRemixBackwardRepository;
use Catrobat\AppBundle\Entity\ScratchProgramRemixRepository;


class RemixGraphManipulator
{
  /**
   * @var EntityManager The entity manager.
   */
  private $entity_manager;

  /**
   * @var RemixSubgraphManipulator The remix subgraph program manipulator.
   */
  private $remix_subgraph_manipulator;

  /**
   * @var ProgramRemixRepository The program remix repository.
   */
  private $program_remix_repository;

  /**
   * @var ProgramRemixBackwardRepository The program remix backward repository.
   */
  private $program_remix_backward_repository;

  /**
   * @var ScratchProgramRemixRepository The scratch program remix repository.
   */
  private $scratch_program_remix_repository;

  /**
   * RemixManager constructor.
   *
   * @param EntityManager                  $entity_manager
   * @param RemixSubgraphManipulator       $remix_subgraph_manipulator
   * @param ProgramRemixRepository         $program_remix_repository
   * @param ProgramRemixBackwardRepository $program_remix_backward_repository
   * @param ScratchProgramRemixRepository  $scratch_program_remix_repository
   */
  public function __construct($entity_manager, $remix_subgraph_manipulator, $program_remix_repository,
                              $program_remix_backward_repository, $scratch_program_remix_repository)
  {
    $this->entity_manager = $entity_manager;
    $this->remix_subgraph_manipulator = $remix_subgraph_manipulator;
    $this->program_remix_repository = $program_remix_repository;
    $this->program_remix_backward_repository = $program_remix_backward_repository;
    $this->scratch_program_remix_repository = $scratch_program_remix_repository;
  }

  /**
   * @param Program $program
   * @param int[]   $removed_forward_parent_ids
   */
  public function convertBackwardParentsHavingNoForwardAncestor(Program $program, array $removed_forward_parent_ids)
  {
    $removed_forward_parents_ancestor_ids = $this->program_remix_repository->getAncestorIds($removed_forward_parent_ids);
    $removed_forward_parents_ancestor_descendant_relations = $this->program_remix_repository->getDescendantRelations($removed_forward_parents_ancestor_ids);

    $program_descendant_ids = $this->program_remix_repository->getDescendantIds([$program->getId()]);

    $backward_relations_to_be_converted = $this
      ->program_remix_backward_repository
      ->getDirectEdgeRelations($program_descendant_ids, $removed_forward_parents_ancestor_ids);

    foreach ($backward_relations_to_be_converted as $backward_relation)
    {
      $cycle_exists = false;
      foreach ($removed_forward_parents_ancestor_descendant_relations as $descendant_relation)
      {
        if ($backward_relation->getParentId() == $descendant_relation->getDescendantId()
          && $backward_relation->getChildId() == $descendant_relation->getAncestorId())
        {
          $cycle_exists = true;
          break;
        }
      }

      if ($cycle_exists)
      {
        continue;
      }

      $preserved_creation_date_mapping = [RemixSubgraphManipulator::COMMON_TIMESTAMP => $backward_relation->getCreatedAt()];
      $preserved_seen_date_mapping = [RemixSubgraphManipulator::COMMON_TIMESTAMP => $backward_relation->getSeenAt()];
      $this->entity_manager->remove($backward_relation);
      $this->remix_subgraph_manipulator->appendRemixSubgraphToCatrobatParents($backward_relation->getChild(),
        [$backward_relation->getParentId()], $preserved_creation_date_mapping, $preserved_seen_date_mapping);
    }

    if (count($backward_relations_to_be_converted) > 0)
    {
      $this->entity_manager->flush();
    }
  }

  /**
   * @param Program $program
   * @param int[]   $all_catrobat_forward_parent_ids
   */
  public function unlinkFromAllCatrobatForwardParents(Program $program, array $all_catrobat_forward_parent_ids)
  {
    $program_id = $program->getId();
    $parents_ancestor_ids = $this->program_remix_repository->getAncestorIds($all_catrobat_forward_parent_ids);
    $program_ancestor_ids = array_merge([$program_id], $parents_ancestor_ids);
    $program_descendant_ids = $program->getCatrobatRemixDescendantIds();

    $direct_and_indirect_descendant_ids = $this->program_remix_repository->getDirectAndIndirectDescendantIds(
      $program_ancestor_ids, $program_descendant_ids);
    $direct_and_indirect_descendant_ids_with_program_id = array_merge([$program_id], $direct_and_indirect_descendant_ids);

    $preserved_edges = $this
      ->program_remix_repository
      ->getDirectEdgeRelationsBetweenProgramIds($parents_ancestor_ids, $direct_and_indirect_descendant_ids);

    $this
      ->program_remix_repository
      ->removeRelationsBetweenProgramIds($parents_ancestor_ids, $direct_and_indirect_descendant_ids_with_program_id);

    foreach ($preserved_edges as $edge)
    {
      $this->remix_subgraph_manipulator->appendRemixSubgraphToCatrobatParents(
        $edge->getDescendant(),
        [$edge->getAncestorId()],
        [RemixSubgraphManipulator::COMMON_TIMESTAMP => $edge->getCreatedAt()],
        [RemixSubgraphManipulator::COMMON_TIMESTAMP => $edge->getSeenAt()]
      );
    }
  }

  /**
   * @param Program $program
   * @param int[]   $catrobat_backward_parent_ids_to_be_removed
   */
  public function unlinkFromCatrobatBackwardParents(Program $program, array $catrobat_backward_parent_ids_to_be_removed)
  {
    $this
      ->program_remix_backward_repository
      ->removeParentRelations($program->getId(), $catrobat_backward_parent_ids_to_be_removed);
  }

  /**
   * @param Program $program
   * @param int[]   $scratch_parent_ids_to_be_removed
   */
  public function unlinkFromScratchParents(Program $program, $scratch_parent_ids_to_be_removed)
  {
    $this
      ->scratch_program_remix_repository
      ->removeParentRelations($program->getId(), $scratch_parent_ids_to_be_removed);
  }

  /**
   * @param Program $program
   * @param int[]   $scratch_parent_ids_to_be_added
   */
  public function linkToScratchParents(Program $program, $scratch_parent_ids_to_be_added)
  {
    foreach ($scratch_parent_ids_to_be_added as $scratch_parent_id)
    {
      $scratch_remix_relation = new ScratchProgramRemixRelation($scratch_parent_id, $program);
      $this->entity_manager->detach($scratch_remix_relation);
      $this->entity_manager->persist($scratch_remix_relation);
      $this->entity_manager->flush();
    }
  }

  /**
   * @param Program $program
   * @param int[]   $ids_of_new_parents
   * @param array   $preserved_creation_date_mapping
   * @param array   $preserved_seen_date_mapping
   */
  public function appendRemixSubgraphToCatrobatParents(Program $program, array $ids_of_new_parents,
                                                       array $preserved_creation_date_mapping,
                                                       array $preserved_seen_date_mapping)
  {
    $this
      ->remix_subgraph_manipulator
      ->appendRemixSubgraphToCatrobatParents($program, $ids_of_new_parents,
        $preserved_creation_date_mapping, $preserved_seen_date_mapping);
  }
}
