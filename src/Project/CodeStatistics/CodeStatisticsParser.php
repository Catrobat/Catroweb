<?php

declare(strict_types=1);

namespace App\Project\CodeStatistics;

use App\DB\Entity\Project\ProjectCodeStatistics;

/**
 * A lightweight regex-based parser for code.xml files that counts bricks, scripts,
 * objects, looks, sounds, and variables without building a full DOM tree.
 */
class CodeStatisticsParser
{
  /**
   * Script types that indicate parallelism (multiple entry points).
   */
  private const array PARALLELISM_SCRIPTS = [
    'WhenClonedScript',
    'BroadcastScript',
    'WhenBackgroundChangesScript',
    'WhenConditionScript',
    'WhenBounceOffScript',
    'CollisionScript',
    'WhenGamepadButtonScript',
    'RaspiInterruptScript',
    'WhenNfcScript',
  ];

  /**
   * Brick types that indicate synchronization (broadcast/wait communication).
   */
  private const array SYNCHRONIZATION_BRICKS = [
    'BroadcastBrick',
    'BroadcastWaitBrick',
    'BroadcastReceiverBrick',
    'WaitBrick',
    'WaitUntilBrick',
  ];

  /**
   * Brick types that indicate logical thinking (conditionals).
   */
  private const array LOGICAL_THINKING_BRICKS = [
    'IfLogicBeginBrick',
    'IfThenLogicBeginBrick',
    'IfLogicElseBrick',
    'PhiroIfLogicBeginBrick',
    'RaspiIfLogicBeginBrick',
  ];

  /**
   * Brick types that indicate flow control (loops).
   */
  private const array FLOW_CONTROL_BRICKS = [
    'ForeverBrick',
    'RepeatBrick',
    'RepeatUntilBrick',
    'ForVariableFromToBrick',
    'ForItemInUserListBrick',
  ];

  /**
   * Brick/script types that indicate user interactivity (sensors/input/touch).
   */
  private const array USER_INTERACTIVITY_TYPES = [
    'WhenScript',
    'WhenTouchDownScript',
    'WhenTouchDownBrick',
    'WhenBrick',
    'AskBrick',
    'AskSpeechBrick',
    'TouchAndSlideBrick',
    'TapAtBrick',
    'TapForBrick',
    'WhenConditionScript',
    'WhenConditionBrick',
  ];

  /**
   * Brick types that indicate data representation (variables/lists).
   */
  private const array DATA_REPRESENTATION_BRICKS = [
    'SetVariableBrick',
    'ChangeVariableBrick',
    'ShowTextBrick',
    'ShowTextColorSizeAlignmentBrick',
    'HideTextBrick',
    'AddItemToUserListBrick',
    'DeleteItemOfUserListBrick',
    'InsertItemIntoUserListBrick',
    'ReplaceItemInUserListBrick',
    'ClearUserListBrick',
    'ReadVariableFromDeviceBrick',
    'WriteVariableOnDeviceBrick',
    'ReadListFromDeviceBrick',
    'WriteListOnDeviceBrick',
    'StoreCSVIntoUserListBrick',
    'WebRequestBrick',
    'ReadVariableFromFileBrick',
    'WriteVariableToFileBrick',
    'UserVariableBrick',
    'UserListBrick',
  ];

  /**
   * Brick/script types that indicate abstraction (user-defined procedures/clones).
   */
  private const array ABSTRACTION_TYPES = [
    'UserDefinedScript',
    'UserDefinedBrick',
    'UserDefinedReceiverBrick',
    'CloneBrick',
    'DeleteThisCloneBrick',
    'WhenClonedScript',
    'WhenClonedBrick',
  ];

  /**
   * Parses a code.xml file and returns a populated ProjectCodeStatistics entity.
   * The entity is NOT persisted; the caller must handle persistence.
   */
  public function parse(string $code_xml_path): ProjectCodeStatistics
  {
    if (!file_exists($code_xml_path)) {
      return new ProjectCodeStatistics();
    }

    $xml_content = file_get_contents($code_xml_path);
    if (false === $xml_content || '' === $xml_content) {
      return new ProjectCodeStatistics();
    }

    // Remove null characters that can appear in code.xml files
    $xml_content = str_replace('&#x0;', '', $xml_content);

    $stats = new ProjectCodeStatistics();

    // Count scenes
    $stats->setScenes($this->countScenes($xml_content));

    // Count scripts by type
    $script_counts = $this->countByTypeAttribute($xml_content, 'script');
    $stats->setScriptCounts($script_counts);
    $stats->setScripts(array_sum($script_counts));

    // Count bricks by type
    $brick_counts = $this->countByTypeAttribute($xml_content, 'brick');
    $stats->setBrickCounts($brick_counts);
    $stats->setBricks(array_sum($brick_counts));

    // Count objects (sprites)
    $stats->setObjects($this->countObjects($xml_content));

    // Count looks
    $stats->setLooks($this->countTag($xml_content, 'look'));

    // Count sounds
    $stats->setSounds($this->countTag($xml_content, 'sound'));

    // Count variables
    $this->countVariables($xml_content, $stats);

    // Compute computational thinking scores
    $all_type_counts = array_merge($script_counts, $brick_counts);
    $this->computeScores($all_type_counts, $stats);

    return $stats;
  }

  /**
   * Counts occurrences of <scene> or <scenes> sections.
   * A project with scenes has <scenes><scene>...</scene></scenes> structure.
   */
  private function countScenes(string $xml_content): int
  {
    // Match <scene> (but not <scenes> or <sceneToStart>)
    if (preg_match_all('/<scene\b[^>]*>/', $xml_content, $matches)) {
      // Filter out <scenes> and <sceneToStart> tags
      $count = 0;
      foreach ($matches[0] as $match) {
        if (!str_starts_with($match, '<scenes') && !str_starts_with($match, '<sceneToStart')) {
          ++$count;
        }
      }

      return $count;
    }

    return 0;
  }

  /**
   * Counts elements with type attributes (e.g., <script type="StartScript">, <brick type="SetXBrick">).
   *
   * @return array<string, int> Map of type name to count
   */
  private function countByTypeAttribute(string $xml_content, string $tag_name): array
  {
    $counts = [];

    // Match <tagName type="TypeName"> patterns
    if (preg_match_all('/<'.$tag_name.'\s+type="([^"]+)"/', $xml_content, $matches)) {
      foreach ($matches[1] as $type_name) {
        $counts[$type_name] = ($counts[$type_name] ?? 0) + 1;
      }
    }

    return $counts;
  }

  /**
   * Counts object/sprite elements. Objects can be represented as:
   * - <object type="SingleSprite" ...>
   * - <object type="GroupItemSprite" ...>
   * - <object type="GroupSprite" ...>
   * - <pointedObject ...> (references, not actual objects)
   */
  private function countObjects(string $xml_content): int
  {
    $count = 0;
    if (preg_match_all('/<object\s+type="([^"]+)"/', $xml_content, $matches)) {
      foreach ($matches[1] as $type) {
        // Count actual sprite types, not group containers
        if ('SingleSprite' === $type || 'GroupItemSprite' === $type) {
          ++$count;
        }
      }
    }

    // Also count objects without a type attribute (older format)
    if (preg_match_all('/<object\s+name="[^"]*"(?!\s+type=)/', $xml_content, $matches)) {
      $count += count($matches[0]);
    }

    return $count;
  }

  /**
   * Counts occurrences of a specific tag (non-self-closing).
   * Counts <look ...> and <sound ...> tags excluding references.
   */
  private function countTag(string $xml_content, string $tag_name): int
  {
    // Match opening tags that have a name or fileName attribute (actual definitions, not references)
    $count = 0;

    // Match <look fileName="..."> or <look name="...">
    if (preg_match_all('/<'.$tag_name.'\b[^>]*(?:fileName|name)="[^"]*"/', $xml_content, $matches)) {
      $count = count($matches[0]);
    }

    return $count;
  }

  /**
   * Counts global and local variables using the same XPath-equivalent logic as the original parser.
   */
  private function countVariables(string $xml_content, ProjectCodeStatistics $stats): void
  {
    // Global variables: in programVariableList or programListOfLists
    $global_vars = 0;
    if (preg_match_all('/<programVariableList>(.*?)<\/programVariableList>/s', $xml_content, $matches)) {
      foreach ($matches[1] as $block) {
        $global_vars += preg_match_all('/<userVariable>/', $block);
      }
    }
    if (preg_match_all('/<programListOfLists>(.*?)<\/programListOfLists>/s', $xml_content, $matches)) {
      foreach ($matches[1] as $block) {
        $global_vars += preg_match_all('/<userVariable>/', $block);
      }
    }

    $stats->setGlobalVariables($global_vars);

    // Count all userVariable occurrences, then subtract globals to get locals
    $total_vars = preg_match_all('/<userVariable>/', $xml_content);

    $local_vars = $total_vars - $global_vars;
    if ($local_vars <= 0) {
      // Fallback for old format
      $local_vars = 0;
      if (preg_match_all('/<objectVariableList>(.*?)<\/objectVariableList>/s', $xml_content, $matches)) {
        foreach ($matches[1] as $block) {
          $local_vars += preg_match_all('/<userVariable>/', $block);
        }
      }
      if (preg_match_all('/<objectListOfList>(.*?)<\/objectListOfList>/s', $xml_content, $matches)) {
        foreach ($matches[1] as $block) {
          $local_vars += preg_match_all('/<userVariable>/', $block);
        }
      }
    }

    $stats->setLocalVariables(max(0, $local_vars));
  }

  /**
   * Computes computational thinking scores based on brick/script type counts.
   *
   * @param array<string, int> $type_counts Combined brick and script type counts
   */
  private function computeScores(array $type_counts, ProjectCodeStatistics $stats): void
  {
    $stats->setScoreAbstraction($this->sumTypeCounts($type_counts, self::ABSTRACTION_TYPES));
    $stats->setScoreParallelism($this->sumTypeCounts($type_counts, self::PARALLELISM_SCRIPTS));
    $stats->setScoreSynchronization($this->sumTypeCounts($type_counts, self::SYNCHRONIZATION_BRICKS));
    $stats->setScoreLogicalThinking($this->sumTypeCounts($type_counts, self::LOGICAL_THINKING_BRICKS));
    $stats->setScoreFlowControl($this->sumTypeCounts($type_counts, self::FLOW_CONTROL_BRICKS));
    $stats->setScoreUserInteractivity($this->sumTypeCounts($type_counts, self::USER_INTERACTIVITY_TYPES));
    $stats->setScoreDataRepresentation($this->sumTypeCounts($type_counts, self::DATA_REPRESENTATION_BRICKS));
  }

  /**
   * Sums counts from the type_counts map for the given type names.
   *
   * @param array<string, int> $type_counts Map of type name to count
   * @param string[]           $type_names  List of type names to sum
   */
  private function sumTypeCounts(array $type_counts, array $type_names): int
  {
    $total = 0;
    foreach ($type_names as $name) {
      $total += $type_counts[$name] ?? 0;
    }

    return $total;
  }
}
