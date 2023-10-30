<?php

namespace App\Project\Remix;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\Remix\ProgramCatrobatRemixRelationInterface;
use App\DB\Entity\Project\Remix\ProgramRemixBackwardRelation;
use App\DB\Entity\Project\Remix\ProgramRemixRelation;
use App\DB\Entity\Project\Remix\ProgramRemixRelationInterface;
use App\DB\Entity\Project\Scratch\ScratchProgram;
use App\DB\Entity\Project\Scratch\ScratchProgramRemixRelation;
use App\DB\Entity\User\Notifications\RemixNotification;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Project\ProgramRemixBackwardRepository;
use App\DB\EntityRepository\Project\ProgramRemixRepository;
use App\DB\EntityRepository\Project\ProgramRepository;
use App\DB\EntityRepository\Project\ScratchProgramRemixRepository;
use App\DB\EntityRepository\Project\ScratchProgramRepository;
use App\User\Notification\NotificationManager;
use App\Utils\TimeUtils;
use Doctrine\ORM\EntityManagerInterface;

class RemixManager
{
  public function __construct(private readonly EntityManagerInterface $entity_manager, private readonly ProgramRepository $program_repository, private readonly ScratchProgramRepository $scratch_program_repository, private readonly ProgramRemixRepository $program_remix_repository, private readonly ProgramRemixBackwardRepository $program_remix_backward_repository, private readonly ScratchProgramRemixRepository $scratch_program_remix_repository, private readonly RemixGraphManipulator $remix_graph_manipulator, private readonly NotificationManager $catro_notification_service)
  {
  }

  public function filterExistingScratchProgramIds(array $scratch_ids): array
  {
    $scratch_program_data = $this->scratch_program_repository->getProgramDataByIds($scratch_ids);

    return array_map(fn ($data) => $data['id'], $scratch_program_data);
  }

  /**
   * @throws \Exception
   */
  public function addScratchPrograms(array $scratch_info_data): void
  {
    foreach ($scratch_info_data as $id => $program_data) {
      $scratch_program = $this->scratch_program_repository->find($id);
      if (null === $scratch_program) {
        $scratch_program = new ScratchProgram($id);
      }

      $title = array_key_exists('title', $program_data) ? $program_data['title'] : null;
      $description = array_key_exists('description', $program_data) ? $program_data['description'] : null;
      $username = null;
      if (array_key_exists('creator', $program_data)) {
        $creator_data = $program_data['creator'];
        $username = array_key_exists('username', $creator_data) ? $creator_data['username'] : null;
      }

      $scratch_program
        ->setName($title)
        ->setDescription($description)
        ->setUsername($username)
      ;

      $this->entity_manager->persist($scratch_program);
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
  public function addRemixes(Program $program, array $remixes_data): void
  {
    // Note: in order to avoid many slow recursive queries (MySql does not support recursive queries yet)
    //       and a lot of complex stored procedures, we simply use Closure tables.
    //       -> All direct and indirect (redundant) relations between programs are stored in the database.

    if (!$program->isInitialVersion()) {
      // case: updated program
      $this->updateProgramRemixRelations($program, $remixes_data);
    } else {
      // case: new program
      $all_program_remix_relations = $this->createNewRemixRelations($program, $remixes_data);
      $catrobat_remix_relations = array_filter($all_program_remix_relations, fn ($relation) => !($relation instanceof ScratchProgramRemixRelation));

      $contains_only_catrobat_self_relation = (1 === count($catrobat_remix_relations));
      $program->setRemixRoot($contains_only_catrobat_self_relation);
      $program->setRemixMigratedAt(TimeUtils::getDateTime());
      $this->entity_manager->persist($program);

      foreach ($all_program_remix_relations as $program_remix_relation) {
        $this->entity_manager->persist($program_remix_relation);
      }

      $this->entity_manager->flush();
    }
  }

  public function getFullRemixGraph(string $program_id): ?array
  {
    static $MAX_RECURSION_DEPTH = 6;
    $recursion_depth = 0;
    $catrobat_ids_of_whole_graph = [$program_id];

    // NOTE: This loop is only needed for exceptional cases (very flat graphs)! In *almost* every case there will
    //       be only *two* loop-iterations. So you can assume that the number of SQL-queries has already been
    //       minimized as much as possible.
    do {
      $previous_descendant_ids = $catrobat_ids_of_whole_graph;

      // TODO: these two queries can be combined!
      $catrobat_root_ids = $this->program_remix_repository->getRootProgramIds($catrobat_ids_of_whole_graph);
      $catrobat_ids_of_whole_graph = $this->program_remix_repository->getDescendantIds($catrobat_root_ids);

      $diff_new = array_diff($catrobat_ids_of_whole_graph, $previous_descendant_ids);
      $diff_previous = array_diff($previous_descendant_ids, $catrobat_ids_of_whole_graph);
      $diff = array_merge($diff_new, $diff_previous);
      $stop_criterion = (0 === count($diff));
    } while (!$stop_criterion && (++$recursion_depth < $MAX_RECURSION_DEPTH));

    sort($catrobat_ids_of_whole_graph);

    $catrobat_forward_edge_relations = $this
      ->program_remix_repository
      ->getDirectEdgeRelationsBetweenProgramIds($catrobat_ids_of_whole_graph, $catrobat_ids_of_whole_graph)
    ;

    $catrobat_forward_relations = $this
      ->program_remix_repository
      ->getDescendantRelations($catrobat_ids_of_whole_graph)
    ;

    $catrobat_forward_edge_data = array_map(fn (ProgramRemixRelation $relation) => [
      'ancestor_id' => $relation->getAncestorId(),
      'descendant_id' => $relation->getDescendantId(),
      'depth' => $relation->getDepth(),
    ], $catrobat_forward_edge_relations);

    $catrobat_forward_data = array_map(fn (ProgramRemixRelation $relation) => [
      'ancestor_id' => $relation->getAncestorId(),
      'descendant_id' => $relation->getDescendantId(),
      'depth' => $relation->getDepth(),
    ], $catrobat_forward_relations);

    $scratch_edge_relations =
      $this->scratch_program_remix_repository->getDirectEdgeRelationsOfProgramIds($catrobat_ids_of_whole_graph);
    $scratch_node_ids = array_values(array_unique(array_map(fn (ScratchProgramRemixRelation $relation) => $relation->getScratchParentId(), $scratch_edge_relations)));
    sort($scratch_node_ids);

    $scratch_edge_data = array_map(fn (ScratchProgramRemixRelation $relation) => [
      'ancestor_id' => $relation->getScratchParentId(),
      'descendant_id' => $relation->getCatrobatChildId(),
    ], $scratch_edge_relations);

    $catrobat_backward_edge_relations = $this
      ->program_remix_backward_repository
      ->getDirectEdgeRelations($catrobat_ids_of_whole_graph, $catrobat_ids_of_whole_graph)
    ;

    $catrobat_backward_edge_data = array_map(fn (ProgramRemixBackwardRelation $relation) => [
      'ancestor_id' => $relation->getParentId(),
      'descendant_id' => $relation->getChildId(),
    ], $catrobat_backward_edge_relations);

    $catrobat_nodes_data = [];
    $programs_data = $this->program_repository->getProjectDataByIds($catrobat_ids_of_whole_graph);
    foreach ($programs_data as $program_data) {
      $catrobat_nodes_data[$program_data['id']] = $program_data;
    }

    $scratch_nodes_data = [];
    $scratch_programs_data = $this->scratch_program_repository->getProgramDataByIds($scratch_node_ids);
    foreach ($scratch_programs_data as $scratch_program_data) {
      $scratch_nodes_data[$scratch_program_data['id']] = $scratch_program_data;
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
      ->program_remix_repository
      ->findOneBy(['ancestor_id' => $ancestor_id, 'descendant_id' => $descendant_id, 'depth' => 1])
    ;

    if (null === $remix_relation) {
      $remix_relation = $this
        ->program_remix_backward_repository
        ->findOneBy(['parent_id' => $ancestor_id, 'child_id' => $descendant_id])
      ;
    }

    return $remix_relation;
  }

  public function removeAllRelations(): void
  {
    $this->program_remix_repository->removeAllRelations();
    $this->program_remix_backward_repository->removeAllRelations();
    $this->scratch_program_remix_repository->removeAllRelations();
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
    $this->program_remix_repository->markAllUnseenRelationsAsSeen($seen_at);
    $this->program_remix_backward_repository->markAllUnseenRelationsAsSeen($seen_at);
  }

  /**
   * @throws \Exception
   */
  public function markRemixRelationAsSeen(ProgramCatrobatRemixRelationInterface $remix_relation): void
  {
    $now = TimeUtils::getDateTime();
    $remix_relation->setSeenAt($now);
    $this->entity_manager->persist($remix_relation);
    $this->entity_manager->flush();
  }

  public function getUnseenRemixProgramsDataOfUser(User $user): array
  {
    $unseen_relations = $this->getUnseenRemixRelationsOfUser($user);
    $unseen_remix_programs_data = [];

    foreach ($unseen_relations as $relation) {
      $original_program = $relation->getAncestor();
      $remixed_program = $relation->getDescendant();
      $remixed_program_user = $remixed_program->getUser();
      $remixed_program_username = $remixed_program_user->getUserIdentifier();

      $unseen_remix_programs_data[] = [
        'originalProgramId' => $original_program->getId(),
        'originalProgramName' => $original_program->getName(),
        'remixProgramId' => $remixed_program->getId(),
        'remixProgramName' => $remixed_program->getName(),
        'remixProgramAuthor' => $remixed_program_username,
        'createdAt' => $relation->getCreatedAt(),
      ];
    }

    return $unseen_remix_programs_data;
  }

  public function remixCount(string $program_id): int
  {
    $result = $this->getFullRemixGraph($program_id);

    if (null === $result) {
      return 0;
    }

    if (null === $result['catrobatNodes'] || 0 === (is_countable($result['catrobatNodes']) ? count($result['catrobatNodes']) : 0)) {
      return 0;
    }

    return (is_countable($result['catrobatNodes']) ? count($result['catrobatNodes']) : 0) - 1;
  }

  public function getProgramRepository(): ProgramRepository
  {
    return $this->program_repository;
  }

  /**
   * @param RemixData[] $remixes_data
   *
   * @return ProgramRemixRelationInterface[]
   */
  private function createNewRemixRelations(Program $program, array $remixes_data): array
  {
    $all_program_remix_relations = [];

    $program_remix_self_relation = new ProgramRemixRelation($program, $program, 0);
    $all_program_remix_relations[$program_remix_self_relation->getUniqueKey()] = $program_remix_self_relation;

    foreach ($remixes_data as $remix_data) {
      $parent_program_id = $remix_data->getProgramId();

      if ($parent_program_id <= 0) {
        continue;
      }

      // case: immediate parent is Scratch program
      if ($remix_data->isScratchProgram()) {
        $scratch_program_remix_relation = new ScratchProgramRemixRelation($parent_program_id, $program);
        $unique_key = $scratch_program_remix_relation->getUniqueKey();
        $all_program_remix_relations[$unique_key] = $scratch_program_remix_relation;
        continue;
      }

      // case: immediate parent is Catrobat program
      /** @var Program|null $parent_program */
      $parent_program = $this->program_repository->find($parent_program_id);
      if (null === $parent_program) {
        continue;
      }

      $remix_notification = new RemixNotification(
        $parent_program->getUser(),
        $program->getUser(),
        $parent_program,
        $program
      );
      $this->catro_notification_service->addNotification($remix_notification);

      $this->createNewCatrobatRemixRelations($program, $parent_program, $all_program_remix_relations);
    }

    return $all_program_remix_relations;
  }

  /**
   * @param ProgramRemixRelationInterface[] $all_program_remix_relations
   */
  private function createNewCatrobatRemixRelations(Program $program, Program $parent_program,
    array &$all_program_remix_relations): void
  {
    $program_remix_relation_to_immediate_parent = new ProgramRemixRelation($parent_program, $program, 1);
    $unique_key = $program_remix_relation_to_immediate_parent->getUniqueKey();
    $all_program_remix_relations[$unique_key] = $program_remix_relation_to_immediate_parent;

    // Catrobat grandparents, parents of grandparents, etc...
    // (i.e. all nodes along all directed paths upwards to roots)
    /** @var ProgramRemixRelation[] $all_parent_ancestor_relations */
    $all_parent_ancestor_relations = $this->program_remix_repository
      ->findBy(['descendant_id' => $parent_program->getId()])
    ;

    foreach ($all_parent_ancestor_relations as $parent_ancestor_relation) {
      $parent_ancestor = $parent_ancestor_relation->getAncestor();
      $parent_ancestor_depth = $parent_ancestor_relation->getDepth();

      $program_remix_relation_to_more_distant_catrobat_ancestor = new ProgramRemixRelation(
        $parent_ancestor,
        $program,
        $parent_ancestor_depth + 1
      );
      $unique_key = $program_remix_relation_to_more_distant_catrobat_ancestor->getUniqueKey();
      $all_program_remix_relations[$unique_key] = $program_remix_relation_to_more_distant_catrobat_ancestor;
    }
  }

  /**
   * @throws \Exception
   */
  private function updateProgramRemixRelations(Program $program, array $remixes_data): void
  {
    $graph_manipulator = $this->remix_graph_manipulator;

    // catrobat parents:
    $catrobat_remixes_data = array_filter($remixes_data, fn (RemixData $remix_data) => !$remix_data->isScratchProgram());
    $new_unfiltered_catrobat_parent_ids = array_map(fn (RemixData $remix_data) => $remix_data->getProgramId(), $catrobat_remixes_data);
    $new_catrobat_parent_ids =
      $this->program_repository->filterExistingProgramIds($new_unfiltered_catrobat_parent_ids);

    $old_forward_ancestor_relations = $program->getCatrobatRemixAncestorRelations()->getValues();
    $old_forward_parent_relations = array_filter($old_forward_ancestor_relations, fn (ProgramRemixRelation $relation) => 1 === $relation->getDepth());
    $old_forward_parent_ids = array_map(fn (ProgramRemixRelation $relation) => $relation->getAncestorId(), $old_forward_parent_relations);

    $preserved_creation_date_mapping = [];
    $preserved_seen_date_mapping = [];

    /** @var ProgramRemixRelation $relation */
    foreach ($old_forward_ancestor_relations as $relation) {
      $preserved_creation_date_mapping[$relation->getUniqueKey()] = $relation->getCreatedAt();
      $preserved_seen_date_mapping[$relation->getUniqueKey()] = $relation->getSeenAt();
    }

    $old_backward_ancestor_relations = $program->getCatrobatRemixBackwardParentRelations()->getValues();
    $old_backward_parent_relations = array_filter($old_backward_ancestor_relations, fn (ProgramRemixBackwardRelation $relation) => 1 === $relation->getDepth());
    $old_backward_parent_ids = array_map(fn (ProgramRemixBackwardRelation $relation) => $relation->getParentId(), $old_backward_parent_relations);
    $old_catrobat_parent_ids = array_unique([...$old_forward_parent_ids, ...$old_backward_parent_ids]);

    $parent_ids_to_be_added = array_values(array_diff($new_catrobat_parent_ids, $old_catrobat_parent_ids));
    $forward_parent_ids_to_be_removed = array_values(array_diff($old_forward_parent_ids, $new_catrobat_parent_ids));
    $backward_parent_ids_to_be_removed = array_values(array_diff($old_backward_parent_ids, $new_catrobat_parent_ids));

    if (count($backward_parent_ids_to_be_removed) > 0) {
      $graph_manipulator->unlinkFromCatrobatBackwardParents($program, $backward_parent_ids_to_be_removed);
    }

    if (count($forward_parent_ids_to_be_removed) > 0) {
      $graph_manipulator->unlinkFromAllCatrobatForwardParents($program, $old_forward_parent_ids);
      $accidentally_removed_forward_parent_ids = array_values(
        array_diff($old_forward_parent_ids, $forward_parent_ids_to_be_removed)
      );
      $parent_ids_to_be_added = array_unique(
        [...$parent_ids_to_be_added, ...$accidentally_removed_forward_parent_ids]
      );
    }

    if (count($parent_ids_to_be_added) > 0) {
      $graph_manipulator->appendRemixSubgraphToCatrobatParents($program, $parent_ids_to_be_added,
        $preserved_creation_date_mapping, $preserved_seen_date_mapping);
    }

    // scratch parents:
    $old_scratch_parent_relations = $program->getScratchRemixParentRelations()->getValues();
    $old_immediate_scratch_parent_ids = array_map(fn (ScratchProgramRemixRelation $relation) => $relation->getScratchParentId(), $old_scratch_parent_relations);

    $scratch_remixes_data = array_filter($remixes_data, fn (RemixData $remix_data) => $remix_data->isScratchProgram());
    $new_scratch_parent_ids = array_map(fn (RemixData $remix_data) => $remix_data->getProgramId(), $scratch_remixes_data);

    $scratch_parent_ids_to_be_added = array_values(
      array_diff($new_scratch_parent_ids, $old_immediate_scratch_parent_ids)
    );
    $scratch_parent_ids_to_be_removed = array_values(
      array_diff($old_immediate_scratch_parent_ids, $new_scratch_parent_ids)
    );

    if (count($scratch_parent_ids_to_be_removed) > 0) {
      $graph_manipulator->unlinkFromScratchParents($program, $scratch_parent_ids_to_be_removed);
    }

    if (count($scratch_parent_ids_to_be_added) > 0) {
      $graph_manipulator->linkToScratchParents($program, $scratch_parent_ids_to_be_added);
    }

    if (count($forward_parent_ids_to_be_removed) > 0) {
      $graph_manipulator->convertBackwardParentsHavingNoForwardAncestor($program, $forward_parent_ids_to_be_removed);
    }

    $new_parent_ancestor_relations = $this->program_remix_repository->getParentAncestorRelations([$program->getId()]);
    $has_no_catrobat_forward_parents = (0 === count($new_parent_ancestor_relations));

    $program->setRemixRoot($has_no_catrobat_forward_parents);
    $program->setRemixMigratedAt(TimeUtils::getDateTime());
    $this->entity_manager->persist($program);
    $this->entity_manager->flush();
  }

  /**
   * @return ProgramCatrobatRemixRelationInterface[]
   */
  private function getUnseenRemixRelationsOfUser(User $user): array
  {
    $forward_relations = $this
      ->program_remix_repository
      ->getUnseenDirectDescendantRelationsOfUser($user)
    ;

    $backward_relations = $this
      ->program_remix_backward_repository
      ->getUnseenChildRelationsOfUser($user)
    ;

    return array_merge($forward_relations, $backward_relations);
  }
}
