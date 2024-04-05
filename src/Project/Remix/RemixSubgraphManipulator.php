<?php

declare(strict_types=1);

namespace App\Project\Remix;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\Remix\ProgramRemixBackwardRelation;
use App\DB\Entity\Project\Remix\ProgramRemixRelation;
use App\DB\EntityRepository\Project\ProgramRemixBackwardRepository;
use App\DB\EntityRepository\Project\ProgramRemixRepository;
use App\DB\EntityRepository\Project\ProgramRepository;
use Doctrine\ORM\EntityManagerInterface;

class RemixSubgraphManipulator
{
  /**
   * @var string
   */
  final public const COMMON_TIMESTAMP = 'common_timestamp';

  public function __construct(private readonly EntityManagerInterface $entity_manager, private readonly ProgramRepository $project_repository, private readonly ProgramRemixRepository $project_remix_repository, private readonly ProgramRemixBackwardRepository $project_remix_backward_repository)
  {
  }

  public function appendRemixSubgraphToCatrobatParents(Program $project, array $ids_of_new_parents,
    array $preserved_creation_date_mapping,
    array $preserved_seen_date_mapping): void
  {
    $project_descendant_relations = $this->project_remix_repository->getDescendantRelations([$project->getId()]);

    $result = $this->splitNewParentIdsByRelationDirection($project_descendant_relations, $ids_of_new_parents);
    $forward_parent_ids = $result['forwardParentIds'];
    $backward_parent_ids = $result['backwardParentIds'];

    $parents_ancestor_relations = $this->project_remix_repository->getAncestorRelations($forward_parent_ids);
    $parent_ancestor_ids = array_unique(array_map(fn ($r) => $r->getAncestorId(), $parents_ancestor_relations));
    $parent_ancestors_descendant_relations = $this->project_remix_repository
      ->getDescendantRelations($parent_ancestor_ids)
    ;
    $backward_parent_relations = $this->project_remix_backward_repository->getParentRelations([$project->getId()]);

    $all_existing_relations = array_merge($parent_ancestors_descendant_relations, $backward_parent_relations);
    $unique_keys_of_all_existing_relations = array_map(fn ($r) => $r->getUniqueKey(), $all_existing_relations);

    $all_project_remix_relations = [];

    // case backward relation:
    foreach ($backward_parent_ids as $backward_parent_id) {
      $parent_project = $this->project_repository->find($backward_parent_id);
      $project_remix_backward_relation = new ProgramRemixBackwardRelation($parent_project, $project);
      $unique_key = $project_remix_backward_relation->getUniqueKey();

      if (!in_array($unique_key, $unique_keys_of_all_existing_relations, true)) {
        $all_project_remix_relations[$unique_key] = $project_remix_backward_relation;
      }
    }

    // case forward relation:
    foreach ($project_descendant_relations as $descendant_relation) {
      foreach ($parents_ancestor_relations as $parent_catrobat_relation) {
        $project_remix_relation = new ProgramRemixRelation(
          $parent_catrobat_relation->getAncestor(),
          $descendant_relation->getDescendant(),
          $parent_catrobat_relation->getDepth() + $descendant_relation->getDepth() + 1
        );

        $unique_key = $project_remix_relation->getUniqueKey();

        if (array_key_exists($unique_key, $preserved_creation_date_mapping)) {
          $project_remix_relation->setCreatedAt($preserved_creation_date_mapping[$unique_key]);
        } elseif (array_key_exists(self::COMMON_TIMESTAMP, $preserved_creation_date_mapping)) {
          $project_remix_relation->setCreatedAt($preserved_creation_date_mapping[self::COMMON_TIMESTAMP]);
        }

        if (array_key_exists($unique_key, $preserved_seen_date_mapping)) {
          $project_remix_relation->setSeenAt($preserved_seen_date_mapping[$unique_key]);
        } elseif (array_key_exists(self::COMMON_TIMESTAMP, $preserved_seen_date_mapping)) {
          $project_remix_relation->setSeenAt($preserved_seen_date_mapping[self::COMMON_TIMESTAMP]);
        }

        if (!in_array($unique_key, $unique_keys_of_all_existing_relations, true)) {
          $all_project_remix_relations[$unique_key] = $project_remix_relation;
        }
      }
    }

    foreach ($all_project_remix_relations as $project_remix_relation) {
      $this->entity_manager->detach($project_remix_relation);
      $this->entity_manager->persist($project_remix_relation);
      $this->entity_manager->flush();
    }
  }

  private function splitNewParentIdsByRelationDirection(array $existing_descendant_relations_of_project,
    array $ids_of_new_parents): array
  {
    // check if any new parent is already an existing child of this project
    // (i.e. has a forward descendant connection to the project)!
    // For all such parents we need a backward connection, because a forward (descendant) connection
    // (that indicates child-relationship) already exists.
    $backward_parent_ids = [];
    foreach ($existing_descendant_relations_of_project as $descendant_relation) {
      if (in_array($descendant_relation->getDescendantId(), $ids_of_new_parents, true)) {
        $backward_parent_ids[] = $descendant_relation->getDescendantId();
      }
    }

    // the rest are forward connections
    $forward_parent_ids = array_diff($ids_of_new_parents, $backward_parent_ids);

    return ['forwardParentIds' => $forward_parent_ids, 'backwardParentIds' => $backward_parent_ids];
  }
}
