<?php

declare(strict_types=1);

namespace App\Project\CodeStatistics;

use App\DB\Entity\Project\ProjectCodeStatistics;
use App\Project\CatrobatCode\Parser\Constants;

/**
 * @phpstan-type RubricContext array{
 *   duplicate_background_targets: bool,
 *   duplicate_broadcast_messages: bool,
 *   duplicate_condition_keys: bool,
 *   has_advanced_sensor: bool,
 *   has_developing_sensor: bool,
 *   has_logical_operator: bool,
 *   has_script_with_brick: bool,
 *   tapped_scripts_same_object: bool,
 *   uses_extension: bool,
 *   uses_physics: bool
 * }
 *
 * @psalm-type RubricContext = array{
 *   duplicate_background_targets: bool,
 *   duplicate_broadcast_messages: bool,
 *   duplicate_condition_keys: bool,
 *   has_advanced_sensor: bool,
 *   has_developing_sensor: bool,
 *   has_logical_operator: bool,
 *   has_script_with_brick: bool,
 *   tapped_scripts_same_object: bool,
 *   uses_extension: bool,
 *   uses_physics: bool
 * }
 *
 * A lightweight parser for code.xml files that keeps the fast aggregate counts
 * but derives computational thinking scores from the rubric levels discussed
 * in the original CT spreadsheet.
 */
class CodeStatisticsParser
{
  final public const string CURRENT_SCORING_VERSION = 'rubric_2021_v2';

  final public const string LEGACY_SCORING_VERSION = 'legacy_keyword_counts_v1';

  private const array ABSTRACTION_TYPES = [
    Constants::USER_DEFINED_SCRIPT,
    'UserDefinedBrick',
    'UserDefinedReceiverBrick',
    Constants::CLONE_BRICK,
    Constants::DELETE_THIS_CLONE_BRICK,
  ];

  private const array LOGICAL_THINKING_BRICKS = [
    Constants::IF_BRICK,
    Constants::IF_THEN_BRICK,
    Constants::PHIRO_IF_LOGIC_BEGIN_BRICK,
    Constants::RASPI_IF_LOGIC_BEGIN_BRICK,
  ];

  private const array FLOW_CONTROL_DEVELOPING_BRICKS = [
    Constants::FOREVER_BRICK,
    Constants::REPEAT_BRICK,
  ];

  private const array FLOW_CONTROL_PROFICIENCY_BRICKS = [
    Constants::REPEAT_UNTIL_BRICK,
  ];

  private const array DATA_REPRESENTATION_BASIC_BRICKS = [
    Constants::PLACE_AT_BRICK,
    Constants::SET_X_BRICK,
    Constants::SET_Y_BRICK,
    Constants::GO_TO_BRICK,
    Constants::CHANGE_X_BY_N_BRICK,
    Constants::CHANGE_Y_BY_N_BRICK,
    Constants::MOVE_N_STEPS_BRICK,
    Constants::SET_SIZE_TO_BRICK,
    Constants::CHANGE_SIZE_BY_N_BRICK,
    Constants::SET_LOOK_BRICK,
    Constants::SET_LOOK_BY_INDEX_BRICK,
    Constants::NEXT_LOOK_BRICK,
    Constants::PREV_LOOK_BRICK,
    Constants::HIDE_BRICK,
    Constants::SHOW_BRICK,
  ];

  private const array DATA_REPRESENTATION_DEVELOPING_BRICKS = [
    'SetVariableBrick',
    'ChangeVariableBrick',
  ];

  private const array DATA_REPRESENTATION_PROFICIENCY_BRICKS = [
    'AddItemToUserListBrick',
    'DeleteItemOfUserListBrick',
    'InsertItemIntoUserListBrick',
    'ReplaceItemInUserListBrick',
    'ClearUserListBrick',
  ];

  private const array USER_INTERACTIVITY_DEVELOPING_TYPES = [
    Constants::ASK_BRICK,
    Constants::WHEN_TOUCH_SCRIPT,
    Constants::WHEN_TOUCH_BRICK,
  ];

  private const array USER_INTERACTIVITY_PROFICIENCY_TYPES = [
    Constants::ASK_SPEECH_BRICK,
    Constants::START_LISTENING_BRICK,
    Constants::CAMERA_BRICK,
    Constants::CHOOSE_CAMERA_BRICK,
  ];

  private const array SYNCHRONIZATION_BASIC_BRICKS = [
    Constants::WAIT_BRICK,
  ];

  private const array SYNCHRONIZATION_DEVELOPING_BRICKS = [
    Constants::BROADCAST_BRICK,
    Constants::STOP_SCRIPT_BRICK,
  ];

  private const array SYNCHRONIZATION_PROFICIENCY_TYPES = [
    Constants::WAIT_UNTIL_BRICK,
    Constants::BROADCAST_WAIT_BRICK,
    Constants::WHEN_BG_CHANGE_SCRIPT,
  ];

  private const array PHYSICS_BRICKS = [
    Constants::SET_PHYSICS_OBJECT_TYPE_BRICK,
    Constants::SET_VELOCITY_BRICK,
    Constants::TURN_LEFT_SPEED_BRICK,
    Constants::TURN_RIGHT_SPEED_BRICK,
    Constants::SET_GRAVITY_BRICK,
    Constants::SET_MASS_BRICK,
    Constants::SET_BOUNCE_BRICK,
    Constants::SET_FRICTION_BRICK,
  ];

  private const array LOGICAL_OPERATOR_VALUES = [
    Constants::AND_OPERATOR,
    Constants::OR_OPERATOR,
    Constants::NOT_OPERATOR,
  ];

  private const array DEVELOPING_INTERACTIVITY_SENSOR_VALUES = [
    'FINGER_TOUCHED',
    'COLLIDES_WITH_FINGER',
    'FINGER_X',
    'FINGER_Y',
    'MULTI_FINGER_X',
    'MULTI_FINGER_Y',
    'MULTI_FINGER_TOUCHED',
    'X_INCLINATION',
    'Y_INCLINATION',
    'LAST_FINGER_INDEX',
    'X_ACCELERATION',
    'Y_ACCELERATION',
    'Z_ACCELERATION',
    'LONGITUDE',
    'LATITUDE',
    'ALTITUDE',
    'LOCATION_ACCURACY',
    'COMPASS_DIRECTION',
  ];

  private const array ADVANCED_INTERACTIVITY_SENSOR_VALUES = [
    'FACE_X_POSITION',
    'FACE_Y_POSITION',
    'FACE_DETECTED',
    'FACE_SIZE',
    'TEXT_FROM_CAMERA',
    'TEXT_BLOCKS_NUMBER',
    'LOUDNESS',
    'SPEECH_RECOGNITION_LANGUAGE',
  ];

  private const array EXTENSION_TYPE_PREFIXES = [
    'Drone',
    'Phiro',
    'Arduino',
    'Raspi',
    'Lego',
    'WhenNfc',
    'SetNfc',
    'WhenGamepad',
    'Gamepad',
    'Embroidery',
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
    $stats->setScoringVersion(self::CURRENT_SCORING_VERSION);

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

    // Compute rubric-based computational thinking scores
    $this->computeScores($xml_content, $script_counts, $brick_counts, $stats);

    return $stats;
  }

  /**
   * Counts occurrences of <scene> or <scenes> sections.
   * A project with scenes has <scenes><scene>...</scene></scenes> structure.
   */
  private function countScenes(string $xml_content): int
  {
    if (preg_match_all('/<scene\b[^>]*>/', $xml_content, $matches)) {
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

    if (preg_match_all('/<'.$tag_name.'\s+type="([^"]+)"/', $xml_content, $matches)) {
      foreach ($matches[1] as $type_name) {
        $counts[$type_name] = ($counts[$type_name] ?? 0) + 1;
      }
    }

    return $counts;
  }

  /**
   * Counts object/sprite elements.
   */
  private function countObjects(string $xml_content): int
  {
    $count = 0;
    if (preg_match_all('/<object\s+type="([^"]+)"/', $xml_content, $matches)) {
      foreach ($matches[1] as $type) {
        if (Constants::SINGLE_SPRITE_TYPE === $type || Constants::GROUP_ITEM_SPRITE_TYPE === $type) {
          ++$count;
        }
      }
    }

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
    $count = 0;

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

    $total_vars = preg_match_all('/<userVariable>/', $xml_content);
    $local_vars = $total_vars - $global_vars;
    if ($local_vars <= 0) {
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
   * Computes rubric-based computational thinking scores.
   *
   * @param array<string, int> $script_counts
   * @param array<string, int> $brick_counts
   */
  private function computeScores(string $xml_content, array $script_counts, array $brick_counts, ProjectCodeStatistics $stats): void
  {
    $type_counts = array_merge($script_counts, $brick_counts);
    $context = $this->buildRubricContext($xml_content, $script_counts, $brick_counts);

    $stats->setScoreAbstraction($this->sumRubricLevels(
      array_sum($script_counts) > 1,
      $this->hasAnyTypeCount($type_counts, self::ABSTRACTION_TYPES),
      ($script_counts[Constants::WHEN_CLONED_SCRIPT] ?? 0) > 0,
    ));

    $stats->setScoreParallelism($this->sumRubricLevels(
      ($script_counts[Constants::START_SCRIPT] ?? 0) > 1,
      $context['tapped_scripts_same_object'],
      $context['duplicate_broadcast_messages']
        || $context['duplicate_background_targets']
        || $context['duplicate_condition_keys'],
    ));

    $stats->setScoreLogicalThinking($this->sumRubricLevels(
      $this->hasAnyTypeCount($brick_counts, self::LOGICAL_THINKING_BRICKS),
      ($brick_counts['IfLogicElseBrick'] ?? 0) > 0,
      $context['has_logical_operator'],
    ));

    $stats->setScoreSynchronization($this->sumRubricLevels(
      $this->hasAnyTypeCount($brick_counts, self::SYNCHRONIZATION_BASIC_BRICKS),
      $this->hasAnyTypeCount($brick_counts, self::SYNCHRONIZATION_DEVELOPING_BRICKS),
      $this->hasAnyTypeCount($type_counts, self::SYNCHRONIZATION_PROFICIENCY_TYPES),
    ));

    $stats->setScoreFlowControl($this->sumRubricLevels(
      $context['has_script_with_brick'],
      $this->hasAnyTypeCount($brick_counts, self::FLOW_CONTROL_DEVELOPING_BRICKS),
      $this->hasAnyTypeCount($brick_counts, self::FLOW_CONTROL_PROFICIENCY_BRICKS),
    ));

    $stats->setScoreUserInteractivity($this->sumRubricLevels(
      ($script_counts[Constants::START_SCRIPT] ?? 0) > 0,
      $this->hasAnyTypeCount($type_counts, self::USER_INTERACTIVITY_DEVELOPING_TYPES)
        || $context['tapped_scripts_same_object']
        || $context['has_developing_sensor'],
      $this->hasAnyTypeCount($type_counts, self::USER_INTERACTIVITY_PROFICIENCY_TYPES)
        || $context['has_advanced_sensor'],
    ));

    $stats->setScoreDataRepresentation($this->sumRubricLevels(
      $this->hasAnyTypeCount($brick_counts, self::DATA_REPRESENTATION_BASIC_BRICKS),
      $this->hasAnyTypeCount($brick_counts, self::DATA_REPRESENTATION_DEVELOPING_BRICKS),
      $this->hasAnyTypeCount($brick_counts, self::DATA_REPRESENTATION_PROFICIENCY_BRICKS),
    ));

    // Breadth bonus: +1 if at least 5 of 7 categories have a score >= 1
    $category_scores = [
      $stats->getScoreAbstraction(),
      $stats->getScoreParallelism(),
      $stats->getScoreLogicalThinking(),
      $stats->getScoreSynchronization(),
      $stats->getScoreFlowControl(),
      $stats->getScoreUserInteractivity(),
      $stats->getScoreDataRepresentation(),
    ];
    $active_categories = count(array_filter($category_scores, static fn (int $s): bool => $s >= 1));
    $breadth_bonus = $active_categories >= 5 ? 1 : 0;

    $stats->setScoreBonus(
      $breadth_bonus
      + ($context['uses_physics'] ? 1 : 0)
      + ($context['uses_extension'] ? 1 : 0),
    );
  }

  /**
   * @param array<string, int> $script_counts
   * @param array<string, int> $brick_counts
   *
   * @return RubricContext
   */
  private function buildRubricContext(string $xml_content, array $script_counts, array $brick_counts): array
  {
    /** @var RubricContext $context */
    $context = [
      'duplicate_background_targets' => false,
      'duplicate_broadcast_messages' => false,
      'duplicate_condition_keys' => false,
      'has_advanced_sensor' => false,
      'has_developing_sensor' => false,
      'has_logical_operator' => false,
      'has_script_with_brick' => false,
      'tapped_scripts_same_object' => false,
      'uses_extension' => $this->usesExtensionType(array_merge(array_keys($script_counts), array_keys($brick_counts))),
      'uses_physics' => $this->hasAnyTypeCount($brick_counts, self::PHYSICS_BRICKS),
    ];

    $xml = @simplexml_load_string($xml_content);
    if (!$xml instanceof \SimpleXMLElement) {
      return $context;
    }

    $broadcast_counts = [];
    foreach ($xml->xpath('//script[@type="'.Constants::BROADCAST_SCRIPT.'"]/receivedMessage') ?: [] as $message_node) {
      $message = $this->normalizeText((string) $message_node);
      if ('' !== $message) {
        $broadcast_counts[$message] = ($broadcast_counts[$message] ?? 0) + 1;
      }
    }
    $context['duplicate_broadcast_messages'] = $this->hasDuplicateCount($broadcast_counts);

    $background_counts = [];
    foreach ($xml->xpath('//script[@type="'.Constants::WHEN_BG_CHANGE_SCRIPT.'"]') ?: [] as $script) {
      $key = $this->extractBackgroundChangeKey($script);
      if ('' !== $key) {
        $background_counts[$key] = ($background_counts[$key] ?? 0) + 1;
      }
    }
    $context['duplicate_background_targets'] = $this->hasDuplicateCount($background_counts);

    $condition_counts = [];
    foreach ($xml->xpath('//script[@type="'.Constants::WHEN_CONDITION_SCRIPT.'"]') ?: [] as $script) {
      $formula_nodes = $script->xpath('./formulaMap/formula');
      $formula = is_array($formula_nodes) ? ($formula_nodes[0] ?? null) : null;
      foreach ($this->extractConditionKeys($formula) as $key) {
        $condition_counts[$key] = ($condition_counts[$key] ?? 0) + 1;
      }
    }
    $context['duplicate_condition_keys'] = $this->hasDuplicateCount($condition_counts);

    foreach ($xml->xpath('//object') ?: [] as $object) {
      $this->analyzeObjectScripts($object, $context);
      if ($context['tapped_scripts_same_object'] && $context['has_script_with_brick']) {
        break;
      }
    }

    $has_logical_operator = false;
    $sensor_values = [];
    foreach ($xml->xpath('//formula') ?: [] as $formula) {
      $this->collectFormulaSignals($formula, $sensor_values, $has_logical_operator);
    }

    $context['has_logical_operator'] = $has_logical_operator;
    $context['has_developing_sensor'] = $this->hasAnySensorValue($sensor_values, self::DEVELOPING_INTERACTIVITY_SENSOR_VALUES);
    $context['has_advanced_sensor'] = $this->hasAnySensorValue($sensor_values, self::ADVANCED_INTERACTIVITY_SENSOR_VALUES);

    return $context;
  }

  /**
   * @param RubricContext $context
   */
  private function analyzeObjectScripts(\SimpleXMLElement $object, array &$context): void
  {
    $script_list = $object->scriptList;
    if (!$script_list instanceof \SimpleXMLElement || !isset($script_list->script)) {
      return;
    }

    $tap_script_count = 0;
    foreach ($script_list->script as $script) {
      $script_type = (string) $script['type'];
      if (isset($script->brickList) && count($script->brickList->brick) > 0) {
        $context['has_script_with_brick'] = true;
      }

      if (Constants::WHEN_TOUCH_SCRIPT === $script_type) {
        ++$tap_script_count;
        continue;
      }

      if (Constants::WHEN_SCRIPT === $script_type) {
        $action = $this->normalizeText((string) $script->action);
        if ('' === $action || 'Tapped' === $action) {
          ++$tap_script_count;
        }
      }
    }

    if ($tap_script_count > 1) {
      $context['tapped_scripts_same_object'] = true;
    }
  }

  private function extractBackgroundChangeKey(\SimpleXMLElement $script): string
  {
    if (isset($script->look)) {
      $reference = $this->normalizeText((string) $script->look['reference']);
      if ('' !== $reference) {
        return 'look-ref:'.$reference;
      }

      $value = $this->normalizeText((string) $script->look);
      if ('' !== $value) {
        return 'look-name:'.$value;
      }
    }

    return '<any-background>';
  }

  /**
   * @return string[]
   */
  private function extractConditionKeys(\SimpleXMLElement|array|null $formula): array
  {
    if (is_array($formula)) {
      $formula = $formula[0] ?? null;
    }

    if (!$formula instanceof \SimpleXMLElement) {
      return [];
    }

    $keys = [];
    $this->collectConditionIdentifiers($formula, $keys);
    $keys = array_values(array_unique(array_filter($keys)));

    if ([] !== $keys) {
      return $keys;
    }

    $xml = $formula->asXML();

    return false === $xml ? [] : ['formula:'.md5($xml)];
  }

  /**
   * @param string[] $keys
   */
  private function collectConditionIdentifiers(\SimpleXMLElement $formula, array &$keys): void
  {
    $type = $this->normalizeText((string) $formula->type);
    $value = $this->normalizeText((string) $formula->value);

    if (('USER_VARIABLE' === $type || 'USER_LIST' === $type) && '' !== $value) {
      $keys[] = 'var:'.$value;
    }

    if ('SENSOR' === $type && '' !== $value) {
      $keys[] = 'sensor:'.$value;
    }

    foreach (['leftChild', 'rightChild'] as $child_name) {
      if (isset($formula->{$child_name})) {
        foreach ($formula->{$child_name} as $child) {
          $this->collectConditionIdentifiers($child, $keys);
        }
      }
    }
  }

  /**
   * @param string[] $sensor_values
   */
  private function collectFormulaSignals(\SimpleXMLElement $formula, array &$sensor_values, bool &$has_logical_operator): void
  {
    $type = $this->normalizeText((string) $formula->type);
    $value = $this->normalizeText((string) $formula->value);

    if ('OPERATOR' === $type && in_array($value, self::LOGICAL_OPERATOR_VALUES, true)) {
      $has_logical_operator = true;
    }

    if (('SENSOR' === $type || 'FUNCTION' === $type) && '' !== $value) {
      $sensor_values[] = $value;
    }

    foreach (['leftChild', 'rightChild'] as $child_name) {
      if (isset($formula->{$child_name})) {
        foreach ($formula->{$child_name} as $child) {
          $this->collectFormulaSignals($child, $sensor_values, $has_logical_operator);
        }
      }
    }
  }

  /**
   * @param string[] $all_type_names
   */
  private function usesExtensionType(array $all_type_names): bool
  {
    foreach ($all_type_names as $type_name) {
      foreach (self::EXTENSION_TYPE_PREFIXES as $prefix) {
        if (str_starts_with($type_name, $prefix)) {
          return true;
        }
      }
    }

    return false;
  }

  /**
   * @param string[] $wanted_values
   * @param string[] $sensor_values
   */
  private function hasAnySensorValue(array $sensor_values, array $wanted_values): bool
  {
    foreach ($sensor_values as $sensor_value) {
      if (in_array($sensor_value, $wanted_values, true)) {
        return true;
      }
    }

    return false;
  }

  /**
   * @param array<string, int> $counts
   * @param string[]           $type_names
   */
  private function hasAnyTypeCount(array $counts, array $type_names): bool
  {
    foreach ($type_names as $type_name) {
      if (($counts[$type_name] ?? 0) > 0) {
        return true;
      }
    }

    return false;
  }

  /**
   * @param array<string, int> $counts
   */
  private function hasDuplicateCount(array $counts): bool
  {
    foreach ($counts as $count) {
      if ($count > 1) {
        return true;
      }
    }

    return false;
  }

  /**
   * Rubric levels are additive: basic contributes 1 point, developing 2 points,
   * and proficiency 3 points, for a maximum of 6 per category.
   */
  private function sumRubricLevels(bool $basic, bool $developing, bool $proficiency): int
  {
    return ($basic ? 1 : 0)
      + ($developing ? 2 : 0)
      + ($proficiency ? 3 : 0);
  }

  private function normalizeText(string $value): string
  {
    return trim($value);
  }
}
