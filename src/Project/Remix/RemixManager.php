<?php

declare(strict_types=1);

namespace App\Project\Remix;

use App\DB\Entity\Project\Project;
use App\DB\Entity\Project\Remix\ProjectRemixBackwardRelation;
use App\DB\Entity\Project\Remix\ProjectRemixRelation;
use App\DB\Entity\Project\Remix\ProjectRemixRelationInterface;
use App\DB\Entity\Project\Scratch\ScratchProject;
use App\DB\Entity\Project\Scratch\ScratchProjectRemixRelation;
use App\DB\Entity\User\Notifications\RemixNotification;
use App\DB\EntityRepository\Project\ProjectRemixBackwardRepository;
use App\DB\EntityRepository\Project\ProjectRemixRepository;
use App\DB\EntityRepository\Project\ProjectRepository;
use App\DB\EntityRepository\Project\ScratchProjectRemixRepository;
use App\DB\EntityRepository\Project\ScratchProjectRepository;
use App\User\Notification\NotificationManager;
use App\Utils\TimeUtils;
use Doctrine\ORM\EntityManagerInterface;

class RemixManager
{
  public function __construct(private readonly EntityManagerInterface $entity_manager, private readonly ProjectRepository $project_repository, private readonly ScratchProjectRepository $scratch_project_repository, private readonly ProjectRemixRepository $project_remix_repository, private readonly ProjectRemixBackwardRepository $project_remix_backward_repository, private readonly ScratchProjectRemixRepository $scratch_project_remix_repository, private readonly RemixGraphManipulator $remix_graph_manipulator, private readonly NotificationManager $catro_notification_service)
  {
  }

  public function filterExistingScratchProjectIds(array $scratch_ids): array
  {
    $scratch_project_data = $this->scratch_project_repository->getProgramDataByIds($scratch_ids);

    return array_map(static fn (array $data) => $data['id'], $scratch_project_data);
  }

  /**
   * @throws \Exception
   */
  public function addScratchProjects(array $scratch_info_data): void
  {
    foreach ($scratch_info_data as $id => $project_data) {
      $id = (string) $id;
      $scratch_project = $this->scratch_project_repository->find($id);
      if (null === $scratch_project) {
        $scratch_project = new ScratchProject($id);
      }

      $title = $project_data['title'] ?? null;
      $description = $project_data['description'] ?? null;
      $username = null;
      if (array_key_exists('creator', $project_data)) {
        $creator_data = $project_data['creator'];
        $username = $creator_data['username'] ?? null;
      }

      $scratch_project
        ->setName($title)
        ->setDescription($description)
        ->setUsername($username)
      ;

      $this->entity_manager->persist($scratch_project);
    }

    if ([] !== $scratch_info_data) {
      $this->entity_manager->flush();
    }
  }

  /**
   * ATTENTION! Internal use only! (no visible/private/debug check).
   *
   * @throws \Exception
   */
  public function addRemixes(Project $project, array $remixes_data): void
  {
    // Note: in order to avoid many slow recursive queries (MySql does not support recursive queries yet)
    //       and a lot of complex stored procedures, we simply use Closure tables.
    //       -> All direct and indirect (redundant) relations between projects are stored in the database.

    if (!$project->isInitialVersion()) {
      // case: updated project
      $this->updateProjectRemixRelations($project, $remixes_data);
    } else {
      // case: new project
      $all_project_remix_relations = $this->createNewRemixRelations($project, $remixes_data);
      $catrobat_remix_relations = array_filter($all_project_remix_relations, static fn (ProjectRemixRelationInterface $relation): bool => !($relation instanceof ScratchProjectRemixRelation));

      $contains_only_catrobat_self_relation = (1 === count($catrobat_remix_relations));
      $project->setRemixRoot($contains_only_catrobat_self_relation);
      $project->setRemixMigratedAt(TimeUtils::getDateTime());
      $this->entity_manager->persist($project);

      foreach ($all_project_remix_relations as $project_remix_relation) {
        $this->entity_manager->persist($project_remix_relation);
      }

      $this->entity_manager->flush();
    }
  }

  public function getRenderableRemixGraph(string $project_id): array
  {
    $catrobat_ids_of_whole_graph = $this->getConnectedCatrobatProgramIds($project_id);

    $catrobat_forward_edge_relations = $this
      ->project_remix_repository
      ->getDirectEdgeRelationsBetweenProgramIds($catrobat_ids_of_whole_graph, $catrobat_ids_of_whole_graph)
    ;

    $scratch_edge_relations =
      $this->scratch_project_remix_repository->getDirectEdgeRelationsOfProgramIds($catrobat_ids_of_whole_graph);
    $scratch_node_ids = array_values(array_unique(array_map(static fn (ScratchProjectRemixRelation $relation): string => $relation->getScratchParentId(), $scratch_edge_relations)));
    sort($scratch_node_ids);

    $catrobat_backward_edge_relations = $this
      ->project_remix_backward_repository
      ->getDirectEdgeRelations($catrobat_ids_of_whole_graph, $catrobat_ids_of_whole_graph)
    ;

    $catrobat_nodes_data = [];
    $projects_data = $this->project_repository->getProjectDataByIdsUnfiltered($catrobat_ids_of_whole_graph);
    foreach ($projects_data as $project_data) {
      $catrobat_nodes_data[$project_data['id']] = $project_data;
    }

    $scratch_nodes_data = [];
    $scratch_projects_data = $this->scratch_project_repository->getProgramDataByIds($scratch_node_ids);
    foreach ($scratch_projects_data as $scratch_project_data) {
      $scratch_nodes_data[$scratch_project_data['id']] = $scratch_project_data;
    }

    $nodes = [];
    foreach ($catrobat_ids_of_whole_graph as $node_id) {
      $nodes[] = [
        'id' => 'catrobat_'.$node_id,
        'projectId' => $node_id,
        'source' => 'catrobat',
        'available' => array_key_exists((string) $node_id, $catrobat_nodes_data),
        'name' => $catrobat_nodes_data[$node_id]['name'] ?? null,
        'username' => $catrobat_nodes_data[$node_id]['username'] ?? null,
      ];
    }

    foreach ($scratch_node_ids as $node_id) {
      $nodes[] = [
        'id' => 'scratch_'.$node_id,
        'projectId' => $node_id,
        'source' => 'scratch',
        'available' => array_key_exists((string) $node_id, $scratch_nodes_data),
        'name' => $scratch_nodes_data[$node_id]['name'] ?? null,
        'username' => $scratch_nodes_data[$node_id]['username'] ?? null,
      ];
    }

    $edges = [];
    $edge_index = 0;

    foreach ($catrobat_forward_edge_relations as $relation) {
      $edges[] = [
        'id' => 'edge_'.$edge_index++,
        'from' => 'catrobat_'.$relation->getAncestorId(),
        'to' => 'catrobat_'.$relation->getDescendantId(),
      ];
    }

    foreach ($catrobat_backward_edge_relations as $relation) {
      $edges[] = [
        'id' => 'edge_'.$edge_index++,
        'from' => 'catrobat_'.$relation->getParentId(),
        'to' => 'catrobat_'.$relation->getChildId(),
      ];
    }

    foreach ($scratch_edge_relations as $relation) {
      $edges[] = [
        'id' => 'edge_'.$edge_index++,
        'from' => 'scratch_'.$relation->getScratchParentId(),
        'to' => 'catrobat_'.$relation->getCatrobatChildId(),
      ];
    }

    return [
      'projectId' => $project_id,
      'projectCount' => count($catrobat_ids_of_whole_graph),
      'scratchCount' => count($scratch_node_ids),
      'remixCount' => max(count($catrobat_ids_of_whole_graph) - 1, 0),
      'nodes' => $nodes,
      'edges' => $edges,
    ];
  }

  public function removeAllRelations(): void
  {
    $this->project_remix_repository->removeAllRelations();
    $this->project_remix_backward_repository->removeAllRelations();
    $this->scratch_project_remix_repository->removeAllRelations();
  }

  public function markAllUnseenRemixRelationsAsSeen(\DateTime $seen_at): void
  {
    $this->project_remix_repository->markAllUnseenRelationsAsSeen($seen_at);
    $this->project_remix_backward_repository->markAllUnseenRelationsAsSeen($seen_at);
  }

  public function getProjectRepository(): ProjectRepository
  {
    return $this->project_repository;
  }

  /**
   * @return string[]
   */
  private function getConnectedCatrobatProgramIds(string $project_id): array
  {
    static $MAX_RECURSION_DEPTH = 6;

    $recursion_depth = 0;
    $catrobat_ids_of_whole_graph = [$project_id];

    // NOTE: This loop is only needed for exceptional cases (very flat graphs)! In almost every case there will
    // be only two loop iterations, so this still keeps the query count low while resolving the connected component.
    do {
      $previous_descendant_ids = $catrobat_ids_of_whole_graph;
      $catrobat_ids_of_whole_graph = $this->project_remix_repository->getGraphDescendantIds($catrobat_ids_of_whole_graph);

      $diff_new = array_diff($catrobat_ids_of_whole_graph, $previous_descendant_ids);
      $diff_previous = array_diff($previous_descendant_ids, $catrobat_ids_of_whole_graph);
      $diff = array_merge($diff_new, $diff_previous);
      $stop_criterion = ([] === $diff);
    } while (!$stop_criterion && (++$recursion_depth < $MAX_RECURSION_DEPTH));

    sort($catrobat_ids_of_whole_graph);

    return array_values(array_unique($catrobat_ids_of_whole_graph));
  }

  /**
   * @param RemixData[] $remixes_data
   *
   * @return ProjectRemixRelationInterface[]
   */
  private function createNewRemixRelations(Project $project, array $remixes_data): array
  {
    $all_project_remix_relations = [];

    $project_remix_self_relation = new ProjectRemixRelation($project, $project, 0);
    $all_project_remix_relations[$project_remix_self_relation->getUniqueKey()] = $project_remix_self_relation;

    foreach ($remixes_data as $remix_data) {
      $parent_project_id = $remix_data->getProjectId();

      if ('' === $parent_project_id) {
        continue;
      }

      // case: immediate parent is Scratch project
      if ($remix_data->isScratchProject()) {
        $scratch_project_remix_relation = new ScratchProjectRemixRelation($parent_project_id, $project);
        $unique_key = $scratch_project_remix_relation->getUniqueKey();
        $all_project_remix_relations[$unique_key] = $scratch_project_remix_relation;
        continue;
      }

      // case: immediate parent is Catrobat project
      /** @var Project|null $parent_project */
      $parent_project = $this->project_repository->find($parent_project_id);
      if (null === $parent_project) {
        continue;
      }

      $remix_notification = new RemixNotification(
        $parent_project->getUser(),
        $project->getUser(),
        $parent_project,
        $project
      );
      $this->catro_notification_service->addNotification($remix_notification);

      $this->createNewCatrobatRemixRelations($project, $parent_project, $all_project_remix_relations);
    }

    return $all_project_remix_relations;
  }

  /**
   * @param ProjectRemixRelationInterface[] $all_project_remix_relations
   */
  private function createNewCatrobatRemixRelations(Project $project, Project $parent_project,
    array &$all_project_remix_relations): void
  {
    $project_remix_relation_to_immediate_parent = new ProjectRemixRelation($parent_project, $project, 1);
    $unique_key = $project_remix_relation_to_immediate_parent->getUniqueKey();
    $all_project_remix_relations[$unique_key] = $project_remix_relation_to_immediate_parent;

    // Catrobat grandparents, parents of grandparents, etc...
    // (i.e. all nodes along all directed paths upwards to roots)
    /** @var ProjectRemixRelation[] $all_parent_ancestor_relations */
    $all_parent_ancestor_relations = $this->project_remix_repository
      ->findBy(['descendant_id' => $parent_project->getId()])
    ;

    foreach ($all_parent_ancestor_relations as $parent_ancestor_relation) {
      $parent_ancestor = $parent_ancestor_relation->getAncestor();
      $parent_ancestor_depth = $parent_ancestor_relation->getDepth();

      $project_remix_relation_to_more_distant_catrobat_ancestor = new ProjectRemixRelation(
        $parent_ancestor,
        $project,
        $parent_ancestor_depth + 1
      );
      $unique_key = $project_remix_relation_to_more_distant_catrobat_ancestor->getUniqueKey();
      $all_project_remix_relations[$unique_key] = $project_remix_relation_to_more_distant_catrobat_ancestor;
    }
  }

  /**
   * @throws \Exception
   */
  private function updateProjectRemixRelations(Project $project, array $remixes_data): void
  {
    $graph_manipulator = $this->remix_graph_manipulator;

    // catrobat parents:
    $catrobat_remixes_data = array_filter($remixes_data, static fn (RemixData $remix_data): bool => !$remix_data->isScratchProject());
    $new_unfiltered_catrobat_parent_ids = array_map(static fn (RemixData $remix_data): string => $remix_data->getProjectId(), $catrobat_remixes_data);
    $new_catrobat_parent_ids =
      $this->project_repository->filterExistingProgramIds($new_unfiltered_catrobat_parent_ids);

    $old_forward_ancestor_relations = $project->getCatrobatRemixAncestorRelations()->getValues();
    $old_forward_parent_relations = array_filter($old_forward_ancestor_relations, static fn (ProjectRemixRelation $relation): bool => 1 === $relation->getDepth());
    $old_forward_parent_ids = array_map(static fn (ProjectRemixRelation $relation): string => $relation->getAncestorId(), $old_forward_parent_relations);

    $preserved_creation_date_mapping = [];
    $preserved_seen_date_mapping = [];

    /** @var ProjectRemixRelation $relation */
    foreach ($old_forward_ancestor_relations as $relation) {
      $preserved_creation_date_mapping[$relation->getUniqueKey()] = $relation->getCreatedAt();
      $preserved_seen_date_mapping[$relation->getUniqueKey()] = $relation->getSeenAt();
    }

    $old_backward_ancestor_relations = $project->getCatrobatRemixBackwardParentRelations()->getValues();
    $old_backward_parent_relations = array_filter($old_backward_ancestor_relations, static fn (ProjectRemixBackwardRelation $relation): bool => 1 === $relation->getDepth());
    $old_backward_parent_ids = array_map(static fn (ProjectRemixBackwardRelation $relation): string => $relation->getParentId(), $old_backward_parent_relations);
    $old_catrobat_parent_ids = array_unique([...$old_forward_parent_ids, ...$old_backward_parent_ids]);

    $parent_ids_to_be_added = array_values(array_diff($new_catrobat_parent_ids, $old_catrobat_parent_ids));
    $forward_parent_ids_to_be_removed = array_values(array_diff($old_forward_parent_ids, $new_catrobat_parent_ids));
    $backward_parent_ids_to_be_removed = array_values(array_diff($old_backward_parent_ids, $new_catrobat_parent_ids));

    if ([] !== $backward_parent_ids_to_be_removed) {
      $graph_manipulator->unlinkFromCatrobatBackwardParents($project, $backward_parent_ids_to_be_removed);
    }

    if ([] !== $forward_parent_ids_to_be_removed) {
      $graph_manipulator->unlinkFromAllCatrobatForwardParents($project, $old_forward_parent_ids);
      $accidentally_removed_forward_parent_ids = array_values(
        array_diff($old_forward_parent_ids, $forward_parent_ids_to_be_removed)
      );
      $parent_ids_to_be_added = array_unique(
        [...$parent_ids_to_be_added, ...$accidentally_removed_forward_parent_ids]
      );
    }

    if ([] !== $parent_ids_to_be_added) {
      $graph_manipulator->appendRemixSubgraphToCatrobatParents($project, $parent_ids_to_be_added,
        $preserved_creation_date_mapping, $preserved_seen_date_mapping);
    }

    // scratch parents:
    $old_scratch_parent_relations = $project->getScratchRemixParentRelations()->getValues();
    $old_immediate_scratch_parent_ids = array_map(static fn (ScratchProjectRemixRelation $relation): string => $relation->getScratchParentId(), $old_scratch_parent_relations);

    $scratch_remixes_data = array_filter($remixes_data, static fn (RemixData $remix_data): bool => $remix_data->isScratchProject());
    $new_scratch_parent_ids = array_map(static fn (RemixData $remix_data): string => $remix_data->getProjectId(), $scratch_remixes_data);

    $scratch_parent_ids_to_be_added = array_values(
      array_diff($new_scratch_parent_ids, $old_immediate_scratch_parent_ids)
    );
    $scratch_parent_ids_to_be_removed = array_values(
      array_diff($old_immediate_scratch_parent_ids, $new_scratch_parent_ids)
    );

    if ([] !== $scratch_parent_ids_to_be_removed) {
      $graph_manipulator->unlinkFromScratchParents($project, $scratch_parent_ids_to_be_removed);
    }

    if ([] !== $scratch_parent_ids_to_be_added) {
      $graph_manipulator->linkToScratchParents($project, $scratch_parent_ids_to_be_added);
    }

    if ([] !== $forward_parent_ids_to_be_removed) {
      $graph_manipulator->convertBackwardParentsHavingNoForwardAncestor($project, $forward_parent_ids_to_be_removed);
    }

    $new_parent_ancestor_relations = $this->project_remix_repository->getParentAncestorRelations([$project->getId()]);
    $has_no_catrobat_forward_parents = ([] === $new_parent_ancestor_relations);

    $project->setRemixRoot($has_no_catrobat_forward_parents);
    $project->setRemixMigratedAt(TimeUtils::getDateTime());

    $this->entity_manager->persist($project);
    $this->entity_manager->flush();
  }
}
