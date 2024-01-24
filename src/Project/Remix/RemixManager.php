<?php

namespace App\Project\Remix;

use App\DB\Entity\Project\Project;
use App\DB\Entity\Project\Remix\ProjectCatrobatRemixRelationInterface;
use App\DB\Entity\Project\Remix\ProjectRemixBackwardRelation;
use App\DB\Entity\Project\Remix\ProjectRemixRelation;
use App\DB\Entity\Project\Remix\ProjectRemixRelationInterface;
use App\DB\Entity\Project\Scratch\ScratchProject;
use App\DB\Entity\Project\Scratch\ScratchProjectRemixRelation;
use App\DB\Entity\User\Notifications\RemixNotification;
use App\DB\Entity\User\User;
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
    $scratch_project_data = $this->scratch_project_repository->getProjectDataByIds($scratch_ids);

    return array_map(fn ($data) => $data['id'], $scratch_project_data);
  }

  /**
   * @throws \Exception
   */
  public function addScratchProjects(array $scratch_info_data): void
  {
    foreach ($scratch_info_data as $id => $project_data) {
      $scratch_project = $this->scratch_project_repository->find($id);
      if (null === $scratch_project) {
        $scratch_project = new ScratchProject($id);
      }

      $title = array_key_exists('title', $project_data) ? $project_data['title'] : null;
      $description = array_key_exists('description', $project_data) ? $project_data['description'] : null;
      $username = null;
      if (array_key_exists('creator', $project_data)) {
        $creator_data = $project_data['creator'];
        $username = array_key_exists('username', $creator_data) ? $creator_data['username'] : null;
      }

      $scratch_project
        ->setName($title)
        ->setDescription($description)
        ->setUsername($username)
      ;

      $this->entity_manager->persist($scratch_project);
    }

    if (count($scratch_info_data) > 0) {
      $this->entity_manager->flush();
    }
  }

  /**
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
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
      $catrobat_remix_relations = array_filter($all_project_remix_relations, fn ($relation) => !($relation instanceof ScratchProjectRemixRelation));

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

  public function getFullRemixGraph(string $project_id): ?array
  {
    static $MAX_RECURSION_DEPTH = 6;
    $recursion_depth = 0;
    $catrobat_ids_of_whole_graph = [$project_id];

    // NOTE: This loop is only needed for exceptional cases (very flat graphs)! In *almost* every case there will
    //       be only *two* loop-iterations. So you can assume that the number of SQL-queries has already been
    //       minimized as much as possible.
    do {
      $previous_descendant_ids = $catrobat_ids_of_whole_graph;

      // TODO: these two queries can be combined!
      $catrobat_root_ids = $this->project_remix_repository->getRootProjectIds($catrobat_ids_of_whole_graph);
      $catrobat_ids_of_whole_graph = $this->project_remix_repository->getDescendantIds($catrobat_root_ids);

      $diff_new = array_diff($catrobat_ids_of_whole_graph, $previous_descendant_ids);
      $diff_previous = array_diff($previous_descendant_ids, $catrobat_ids_of_whole_graph);
      $diff = array_merge($diff_new, $diff_previous);
      $stop_criterion = (0 === count($diff));
    } while (!$stop_criterion && (++$recursion_depth < $MAX_RECURSION_DEPTH));

    sort($catrobat_ids_of_whole_graph);

    $catrobat_forward_edge_relations = $this
      ->project_remix_repository
      ->getDirectEdgeRelationsBetweenProjectIds($catrobat_ids_of_whole_graph, $catrobat_ids_of_whole_graph)
    ;

    $catrobat_forward_relations = $this
      ->project_remix_repository
      ->getDescendantRelations($catrobat_ids_of_whole_graph)
    ;

    $catrobat_forward_edge_data = array_map(fn (ProjectRemixRelation $relation) => [
      'ancestor_id' => $relation->getAncestorId(),
      'descendant_id' => $relation->getDescendantId(),
      'depth' => $relation->getDepth(),
    ], $catrobat_forward_edge_relations);

    $catrobat_forward_data = array_map(fn (ProjectRemixRelation $relation) => [
      'ancestor_id' => $relation->getAncestorId(),
      'descendant_id' => $relation->getDescendantId(),
      'depth' => $relation->getDepth(),
    ], $catrobat_forward_relations);

    $scratch_edge_relations =
      $this->scratch_project_remix_repository->getDirectEdgeRelationsOfProjectIds($catrobat_ids_of_whole_graph);
    $scratch_node_ids = array_values(array_unique(array_map(fn (ScratchProjectRemixRelation $relation) => $relation->getScratchParentId(), $scratch_edge_relations)));
    sort($scratch_node_ids);

    $scratch_edge_data = array_map(fn (ScratchProjectRemixRelation $relation) => [
      'ancestor_id' => $relation->getScratchParentId(),
      'descendant_id' => $relation->getCatrobatChildId(),
    ], $scratch_edge_relations);

    $catrobat_backward_edge_relations = $this
      ->project_remix_backward_repository
      ->getDirectEdgeRelations($catrobat_ids_of_whole_graph, $catrobat_ids_of_whole_graph)
    ;

    $catrobat_backward_edge_data = array_map(fn (ProjectRemixBackwardRelation $relation) => [
      'ancestor_id' => $relation->getParentId(),
      'descendant_id' => $relation->getChildId(),
    ], $catrobat_backward_edge_relations);

    $catrobat_nodes_data = [];
    $projects_data = $this->project_repository->getProjectDataByIds($catrobat_ids_of_whole_graph);
    foreach ($projects_data as $project_data) {
      $catrobat_nodes_data[$project_data['id']] = $project_data;
    }

    $scratch_nodes_data = [];
    $scratch_projects_data = $this->scratch_project_repository->getProjectDataByIds($scratch_node_ids);
    foreach ($scratch_projects_data as $scratch_project_data) {
      $scratch_nodes_data[$scratch_project_data['id']] = $scratch_project_data;
    }

    return [
      'catrobatNodes' => $catrobat_ids_of_whole_graph,
      'catrobatNodesData' => $catrobat_nodes_data,
      'scratchNodes' => $scratch_node_ids,
      'scratchNodesData' => $scratch_nodes_data,
      'catrobatForwardEdgeRelations' => $catrobat_forward_edge_data,
      'catrobatBackwardEdgeRelations' => $catrobat_backward_edge_data,
      'catrobatForwardRelations' => $catrobat_forward_data,
      'scratchEdgeRelations' => $scratch_edge_data,
    ];
  }

  public function findCatrobatRelation(string $ancestor_id, string $descendant_id): mixed
  {
    $remix_relation = $this
      ->project_remix_repository
      ->findOneBy(['ancestor_id' => $ancestor_id, 'descendant_id' => $descendant_id, 'depth' => 1])
    ;

    if (null === $remix_relation) {
      $remix_relation = $this
        ->project_remix_backward_repository
        ->findOneBy(['parent_id' => $ancestor_id, 'child_id' => $descendant_id])
      ;
    }

    return $remix_relation;
  }

  public function removeAllRelations(): void
  {
    $this->project_remix_repository->removeAllRelations();
    $this->project_remix_backward_repository->removeAllRelations();
    $this->scratch_project_remix_repository->removeAllRelations();
  }

  /**
   * @throws \Exception
   */
  public function markAllUnseenRemixRelationsOfUserAsSeen(User $user): void
  {
    $unseen_relations = $this->getUnseenRemixRelationsOfUser($user);
    $now = TimeUtils::getDateTime();
    foreach ($unseen_relations as $relation) {
      $relation->setSeenAt($now);
      $this->entity_manager->persist($relation);
    }
    $this->entity_manager->flush();
  }

  public function markAllUnseenRemixRelationsAsSeen(\DateTime $seen_at): void
  {
    $this->project_remix_repository->markAllUnseenRelationsAsSeen($seen_at);
    $this->project_remix_backward_repository->markAllUnseenRelationsAsSeen($seen_at);
  }

  /**
   * @throws \Exception
   */
  public function markRemixRelationAsSeen(ProjectCatrobatRemixRelationInterface $remix_relation): void
  {
    $now = TimeUtils::getDateTime();
    $remix_relation->setSeenAt($now);
    $this->entity_manager->persist($remix_relation);
    $this->entity_manager->flush();
  }

  public function getUnseenRemixProjectsDataOfUser(User $user): array
  {
    $unseen_relations = $this->getUnseenRemixRelationsOfUser($user);
    $unseen_remix_projects_data = [];

    foreach ($unseen_relations as $relation) {
      $original_project = $relation->getAncestor();
      $remixed_project = $relation->getDescendant();
      $remixed_project_user = $remixed_project->getUser();
      $remixed_project_username = $remixed_project_user->getUserIdentifier();

      $unseen_remix_projects_data[] = [
        'originalProgramId' => $original_project->getId(),
        'originalProgramName' => $original_project->getName(),
        'remixProgramId' => $remixed_project->getId(),
        'remixProgramName' => $remixed_project->getName(),
        'remixProgramAuthor' => $remixed_project_username,
        'createdAt' => $relation->getCreatedAt(),
      ];
    }

    return $unseen_remix_projects_data;
  }

  public function remixCount(string $project_id): int
  {
    $result = $this->getFullRemixGraph($project_id);

    if (null === $result) {
      return 0;
    }

    if (null === $result['catrobatNodes'] || 0 === (is_countable($result['catrobatNodes']) ? count($result['catrobatNodes']) : 0)) {
      return 0;
    }

    return (is_countable($result['catrobatNodes']) ? count($result['catrobatNodes']) : 0) - 1;
  }

  public function getProjectRepository(): ProjectRepository
  {
    return $this->project_repository;
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

      if ($parent_project_id <= 0) {
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
    $catrobat_remixes_data = array_filter($remixes_data, fn (RemixData $remix_data) => !$remix_data->isScratchProject());
    $new_unfiltered_catrobat_parent_ids = array_map(fn (RemixData $remix_data) => $remix_data->getProjectId(), $catrobat_remixes_data);
    $new_catrobat_parent_ids =
      $this->project_repository->filterExistingProjectIds($new_unfiltered_catrobat_parent_ids);

    $old_forward_ancestor_relations = $project->getCatrobatRemixAncestorRelations()->getValues();
    $old_forward_parent_relations = array_filter($old_forward_ancestor_relations, fn (ProjectRemixRelation $relation) => 1 === $relation->getDepth());
    $old_forward_parent_ids = array_map(fn (ProjectRemixRelation $relation) => $relation->getAncestorId(), $old_forward_parent_relations);

    $preserved_creation_date_mapping = [];
    $preserved_seen_date_mapping = [];

    /** @var ProjectRemixRelation $relation */
    foreach ($old_forward_ancestor_relations as $relation) {
      $preserved_creation_date_mapping[$relation->getUniqueKey()] = $relation->getCreatedAt();
      $preserved_seen_date_mapping[$relation->getUniqueKey()] = $relation->getSeenAt();
    }

    $old_backward_ancestor_relations = $project->getCatrobatRemixBackwardParentRelations()->getValues();
    $old_backward_parent_relations = array_filter($old_backward_ancestor_relations, fn (ProjectRemixBackwardRelation $relation) => 1 === $relation->getDepth());
    $old_backward_parent_ids = array_map(fn (ProjectRemixBackwardRelation $relation) => $relation->getParentId(), $old_backward_parent_relations);
    $old_catrobat_parent_ids = array_unique([...$old_forward_parent_ids, ...$old_backward_parent_ids]);

    $parent_ids_to_be_added = array_values(array_diff($new_catrobat_parent_ids, $old_catrobat_parent_ids));
    $forward_parent_ids_to_be_removed = array_values(array_diff($old_forward_parent_ids, $new_catrobat_parent_ids));
    $backward_parent_ids_to_be_removed = array_values(array_diff($old_backward_parent_ids, $new_catrobat_parent_ids));

    if (count($backward_parent_ids_to_be_removed) > 0) {
      $graph_manipulator->unlinkFromCatrobatBackwardParents($project, $backward_parent_ids_to_be_removed);
    }

    if (count($forward_parent_ids_to_be_removed) > 0) {
      $graph_manipulator->unlinkFromAllCatrobatForwardParents($project, $old_forward_parent_ids);
      $accidentally_removed_forward_parent_ids = array_values(
        array_diff($old_forward_parent_ids, $forward_parent_ids_to_be_removed)
      );
      $parent_ids_to_be_added = array_unique(
        [...$parent_ids_to_be_added, ...$accidentally_removed_forward_parent_ids]
      );
    }

    if (count($parent_ids_to_be_added) > 0) {
      $graph_manipulator->appendRemixSubgraphToCatrobatParents($project, $parent_ids_to_be_added,
        $preserved_creation_date_mapping, $preserved_seen_date_mapping);
    }

    // scratch parents:
    $old_scratch_parent_relations = $project->getScratchRemixParentRelations()->getValues();
    $old_immediate_scratch_parent_ids = array_map(fn (ScratchProjectRemixRelation $relation) => $relation->getScratchParentId(), $old_scratch_parent_relations);

    $scratch_remixes_data = array_filter($remixes_data, fn (RemixData $remix_data) => $remix_data->isScratchProject());
    $new_scratch_parent_ids = array_map(fn (RemixData $remix_data) => $remix_data->getProjectId(), $scratch_remixes_data);

    $scratch_parent_ids_to_be_added = array_values(
      array_diff($new_scratch_parent_ids, $old_immediate_scratch_parent_ids)
    );
    $scratch_parent_ids_to_be_removed = array_values(
      array_diff($old_immediate_scratch_parent_ids, $new_scratch_parent_ids)
    );

    if (count($scratch_parent_ids_to_be_removed) > 0) {
      $graph_manipulator->unlinkFromScratchParents($project, $scratch_parent_ids_to_be_removed);
    }

    if (count($scratch_parent_ids_to_be_added) > 0) {
      $graph_manipulator->linkToScratchParents($project, $scratch_parent_ids_to_be_added);
    }

    if (count($forward_parent_ids_to_be_removed) > 0) {
      $graph_manipulator->convertBackwardParentsHavingNoForwardAncestor($project, $forward_parent_ids_to_be_removed);
    }

    $new_parent_ancestor_relations = $this->project_remix_repository->getParentAncestorRelations([$project->getId()]);
    $has_no_catrobat_forward_parents = (0 === count($new_parent_ancestor_relations));

    $project->setRemixRoot($has_no_catrobat_forward_parents);
    $project->setRemixMigratedAt(TimeUtils::getDateTime());
    $this->entity_manager->persist($project);
    $this->entity_manager->flush();
  }

  /**
   * @return ProjectCatrobatRemixRelationInterface[]
   */
  private function getUnseenRemixRelationsOfUser(User $user): array
  {
    $forward_relations = $this
      ->project_remix_repository
      ->getUnseenDirectDescendantRelationsOfUser($user)
    ;

    $backward_relations = $this
      ->project_remix_backward_repository
      ->getUnseenChildRelationsOfUser($user)
    ;

    return array_merge($forward_relations, $backward_relations);
  }
}
