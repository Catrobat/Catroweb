<?php

declare(strict_types=1);

namespace App\Project\Remix;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\Scratch\ScratchProgramRemixRelation;
use App\DB\EntityRepository\Project\ProgramRemixBackwardRepository;
use App\DB\EntityRepository\Project\ProgramRemixRepository;
use App\DB\EntityRepository\Project\ScratchProgramRemixRepository;
use Doctrine\ORM\EntityManagerInterface;

class RemixGraphManipulator
{
  public function __construct(private readonly EntityManagerInterface $entity_manager, private readonly RemixSubgraphManipulator $remix_subgraph_manipulator, private readonly ProgramRemixRepository $project_remix_repository, private readonly ProgramRemixBackwardRepository $project_remix_backward_repository, private readonly ScratchProgramRemixRepository $scratch_project_remix_repository)
  {
  }

  public function convertBackwardParentsHavingNoForwardAncestor(Program $project, array $removed_forward_parent_ids): void
  {
    $removed_forward_parents_ancestor_ids = $this->project_remix_repository->getAncestorIds($removed_forward_parent_ids);
    $removed_forward_parents_ancestor_descendant_relations = $this->project_remix_repository->getDescendantRelations($removed_forward_parents_ancestor_ids);

    $project_descendant_ids = $this->project_remix_repository->getDescendantIds([$project->getId()]);

    $backward_relations_to_be_converted = $this
      ->project_remix_backward_repository
      ->getDirectEdgeRelations($project_descendant_ids, $removed_forward_parents_ancestor_ids)
    ;

    foreach ($backward_relations_to_be_converted as $backward_relation) {
      $cycle_exists = false;
      foreach ($removed_forward_parents_ancestor_descendant_relations as $descendant_relation) {
        if ($backward_relation->getParentId() === $descendant_relation->getDescendantId()
          && $backward_relation->getChildId() === $descendant_relation->getAncestorId()) {
          $cycle_exists = true;
          break;
        }
      }

      if ($cycle_exists) {
        continue;
      }

      $preserved_creation_date_mapping = [RemixSubgraphManipulator::COMMON_TIMESTAMP => $backward_relation->getCreatedAt()];
      $preserved_seen_date_mapping = [RemixSubgraphManipulator::COMMON_TIMESTAMP => $backward_relation->getSeenAt()];
      $this->entity_manager->remove($backward_relation);
      $this->remix_subgraph_manipulator->appendRemixSubgraphToCatrobatParents($backward_relation->getChild(),
        [$backward_relation->getParentId()], $preserved_creation_date_mapping, $preserved_seen_date_mapping);
    }

    if (count($backward_relations_to_be_converted) > 0) {
      $this->entity_manager->flush();
    }
  }

  /**
   * @param string[] $all_catrobat_forward_parent_ids
   */
  public function unlinkFromAllCatrobatForwardParents(Program $project, array $all_catrobat_forward_parent_ids): void
  {
    $project_id = $project->getId();
    $parents_ancestor_ids = $this->project_remix_repository->getAncestorIds($all_catrobat_forward_parent_ids);
    $project_ancestor_ids = array_merge([$project_id], $parents_ancestor_ids);
    $project_descendant_ids = $project->getCatrobatRemixDescendantIds();

    $direct_and_indirect_descendant_ids = $this->project_remix_repository->getDirectAndIndirectDescendantIds(
      $project_ancestor_ids, $project_descendant_ids
    );
    $direct_and_indirect_descendant_ids_with_project_id = array_merge([$project_id], $direct_and_indirect_descendant_ids);

    $preserved_edges = $this
      ->project_remix_repository
      ->getDirectEdgeRelationsBetweenProgramIds($parents_ancestor_ids, $direct_and_indirect_descendant_ids)
    ;

    $this
      ->project_remix_repository
      ->removeRelationsBetweenProgramIds($parents_ancestor_ids, $direct_and_indirect_descendant_ids_with_project_id)
    ;

    foreach ($preserved_edges as $edge) {
      $this->remix_subgraph_manipulator->appendRemixSubgraphToCatrobatParents(
        $edge->getDescendant(),
        [$edge->getAncestorId()],
        [RemixSubgraphManipulator::COMMON_TIMESTAMP => $edge->getCreatedAt()],
        [RemixSubgraphManipulator::COMMON_TIMESTAMP => $edge->getSeenAt()]
      );
    }
  }

  public function unlinkFromCatrobatBackwardParents(Program $project, array $catrobat_backward_parent_ids_to_be_removed): void
  {
    $this
      ->project_remix_backward_repository
      ->removeParentRelations($project->getId(), $catrobat_backward_parent_ids_to_be_removed)
    ;
  }

  public function unlinkFromScratchParents(Program $project, array $scratch_parent_ids_to_be_removed): void
  {
    $this
      ->scratch_project_remix_repository
      ->removeParentRelations($project->getId(), $scratch_parent_ids_to_be_removed)
    ;
  }

  public function linkToScratchParents(Program $project, array $scratch_parent_ids_to_be_added): void
  {
    foreach ($scratch_parent_ids_to_be_added as $scratch_parent_id) {
      $scratch_remix_relation = new ScratchProgramRemixRelation((string) $scratch_parent_id, $project);
      $this->entity_manager->detach($scratch_remix_relation);
      $this->entity_manager->persist($scratch_remix_relation);
      $this->entity_manager->flush();
    }
  }

  public function appendRemixSubgraphToCatrobatParents(Program $project, array $ids_of_new_parents,
    array $preserved_creation_date_mapping,
    array $preserved_seen_date_mapping): void
  {
    $this
      ->remix_subgraph_manipulator
      ->appendRemixSubgraphToCatrobatParents($project, $ids_of_new_parents,
        $preserved_creation_date_mapping, $preserved_seen_date_mapping)
    ;
  }
}
