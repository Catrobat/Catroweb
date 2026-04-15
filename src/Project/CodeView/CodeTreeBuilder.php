<?php

declare(strict_types=1);

namespace App\Project\CodeView;

use App\DB\Entity\Project\Project;
use App\Project\CatrobatCode\Parser\Bricks\Brick;
use App\Project\CatrobatCode\Parser\Bricks\BrickFactory;
use App\Project\CatrobatCode\Parser\CatrobatCodeParser;
use App\Project\CatrobatCode\Parser\Constants;
use App\Project\CatrobatCode\Parser\FormulaResolver;
use App\Project\CatrobatCode\Parser\ParsedObject;
use App\Project\CatrobatCode\Parser\ParsedObjectAsset;
use App\Project\CatrobatCode\Parser\ParsedObjectGroup;
use App\Project\CatrobatCode\Parser\ParsedObjectsContainer;
use App\Project\CatrobatCode\Parser\ParsedScene;
use App\Project\CatrobatCode\Parser\ParsedSceneProject;
use App\Project\CatrobatCode\Parser\ParsedSimpleProject;
use App\Project\CatrobatCode\Parser\Scripts\Script;
use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\CatrobatFile\ExtractedFileRepository;

class CodeTreeBuilder
{
  /**
   * Maps brick image files to category names.
   * Some image constants share the same filename (e.g. PHIRO_CONTROL_BRICK_IMG = CONTROL_BRICK_IMG);
   * only unique image filenames are listed here.
   */
  private const array IMG_TO_CATEGORY = [
    '1h_when_brown.png' => 'event',
    '1h_brick_brown.png' => 'event',
    '1h_when_orange.png' => 'control',
    '1h_brick_orange.png' => 'control',
    '1h_brick_blue.png' => 'motion',
    '1h_when_blue.png' => 'motion',
    '1h_brick_violet.png' => 'sound',
    '1h_brick_green.png' => 'looks',
    '1h_brick_red.png' => 'data',
    '1h_brick_darkgreen.png' => 'pen',
    '1h_brick_gold.png' => 'device',
    '1h_brick_yellow.png' => 'lego',
    '1h_brick_light_blue.png' => 'extension',
    '1h_brick_pink.png' => 'embroidery',
    '1h_when_grey.png' => 'unknown',
    '1h_brick_grey.png' => 'unknown',
  ];

  /**
   * Brick types that start a loop (their children are in loopBricks).
   */
  private const array LOOP_BRICK_TYPES = [
    Constants::FOREVER_BRICK,
    Constants::REPEAT_BRICK,
    Constants::REPEAT_UNTIL_BRICK,
    Constants::FOR_VARIABLE_FROM_TO_BRICK,
    Constants::FOR_ITEM_IN_USER_LIST_BRICK,
    Constants::LOOP_ENDLESS_BRICK,
  ];

  /**
   * Brick types that start an if/else control structure.
   */
  private const array IF_BRICK_TYPES = [
    Constants::IF_BRICK,
    Constants::IF_THEN_BRICK,
    Constants::RASPI_IF_LOGIC_BEGIN_BRICK,
    Constants::PHIRO_IF_LOGIC_BEGIN_BRICK,
  ];

  public function __construct(
    private readonly ExtractedFileRepository $extracted_file_repository,
    private readonly CatrobatCodeParser $parser,
  ) {
  }

  /**
   * Build the code tree for a project.
   *
   * @return array{scenes: list<array>}
   *
   * @throws CodeTreeBuildException
   */
  public function buildCodeTree(Project $project): array
  {
    $extracted = $this->extracted_file_repository->loadProjectExtractedFile($project);
    if (null === $extracted) {
      throw new CodeTreeBuildException('Could not load project files');
    }

    return $this->buildFromExtracted($extracted);
  }

  /**
   * Build the code tree from an already-extracted project file.
   *
   * @return array{scenes: list<array>}
   *
   * @throws CodeTreeBuildException
   */
  public function buildFromExtracted(ExtractedCatrobatFile $extracted): array
  {
    $parsed = $this->parser->parse($extracted);
    if (null === $parsed) {
      throw new CodeTreeBuildException('Failed to parse project XML');
    }

    $web_path = '/'.ltrim($extracted->getWebPath(), '/');

    if ($parsed instanceof ParsedSceneProject) {
      return $this->buildFromSceneProject($parsed, $web_path);
    }

    return $this->buildFromSimpleProject($parsed, $web_path);
  }

  /**
   * @return array{scenes: list<array>}
   */
  private function buildFromSceneProject(ParsedSceneProject $project, string $web_path): array
  {
    $scenes = [];
    foreach ($project->getScenes() as $scene) {
      $scene_name = (string) $scene->getName();
      $asset_base = $web_path.$scene_name.'/';
      $scenes[] = $this->buildScene($scene, $asset_base);
    }

    return ['scenes' => $scenes];
  }

  /**
   * @return array{scenes: list<array>}
   */
  private function buildFromSimpleProject(ParsedSimpleProject $project, string $web_path): array
  {
    $objects = $this->buildObjectList($project, $web_path);

    return ['scenes' => [
      [
        'name' => 'default',
        'objects' => $objects,
      ],
    ]];
  }

  /**
   * @return array{name: string, objects: list<array>}
   */
  private function buildScene(ParsedScene $scene, string $asset_base): array
  {
    return [
      'name' => (string) $scene->getName(),
      'objects' => $this->buildObjectList($scene, $asset_base),
    ];
  }

  /**
   * @return list<array>
   */
  private function buildObjectList(ParsedObjectsContainer $container, string $asset_base): array
  {
    $objects = [];

    $background = $container->getBackground();
    if (null !== $background) {
      $objects[] = $this->buildObject($background, $asset_base);
    }

    foreach ($container->getObjects() as $object) {
      if ($object instanceof ParsedObjectGroup) {
        $objects[] = $this->buildGroup($object, $asset_base);
      } elseif ($object instanceof ParsedObject) {
        $objects[] = $this->buildObject($object, $asset_base);
      }
    }

    return $objects;
  }

  /**
   * @return array{name: string, is_group: bool, scripts: list<array>, looks: list<array>, sounds: list<array>, children: null}
   */
  private function buildObject(ParsedObject $object, string $asset_base): array
  {
    $scripts = [];
    foreach ($object->getScripts() as $script) {
      $scripts[] = $this->buildScript($script);
    }

    $looks = $this->buildAssetList($object->getLooks(), $asset_base.'images/');

    return [
      'name' => (string) $object->getName(),
      'is_group' => false,
      'scripts' => $scripts,
      'looks' => $looks,
      'sounds' => $this->buildAssetList($object->getSounds(), $asset_base.'sounds/'),
      'children' => null,
    ];
  }

  /**
   * @return array{name: string, is_group: bool, scripts: list<array>, looks: list<array>, sounds: list<array>, children: list<array>}
   */
  private function buildGroup(ParsedObjectGroup $group, string $asset_base): array
  {
    $children = [];
    foreach ($group->getObjects() as $childObject) {
      if ($childObject instanceof ParsedObject) {
        $children[] = $this->buildObject($childObject, $asset_base);
      }
    }

    return [
      'name' => (string) $group->getName(),
      'is_group' => true,
      'scripts' => [],
      'looks' => [],
      'sounds' => [],
      'children' => $children,
    ];
  }

  /**
   * @param ParsedObjectAsset[] $assets
   *
   * @return list<array{name: string, url: string|null}>
   */
  private function buildAssetList(array $assets, string $base_url): array
  {
    $result = [];
    foreach ($assets as $asset) {
      $file_name = $asset->getFileName();
      $result[] = [
        'name' => $asset->getName() ?? $file_name ?? '',
        'url' => (null !== $file_name && '' !== $file_name) ? $base_url.$file_name : null,
      ];
    }

    return $result;
  }

  /**
   * @return array{type: string, category: string, display_text: string, commented_out: bool, bricks: list<array>}
   */
  private function buildScript(Script $script): array
  {
    return [
      'type' => $script->getType(),
      'category' => $this->resolveCategory($script->getImgFile()),
      'display_text' => $this->buildScriptDisplayText($script),
      'commented_out' => Constants::UNKNOWN_SCRIPT_IMG === $script->getImgFile(),
      'bricks' => $this->buildNestedBrickList($script),
    ];
  }

  private function buildScriptDisplayText(Script $script): string
  {
    $caption = $script->getCaption();
    if (!str_contains($caption, '_')) {
      return $caption;
    }

    $reflection = new \ReflectionProperty(Script::class, 'script_xml_properties');
    $xml = $reflection->getValue($script);
    if (!$xml instanceof \SimpleXMLElement) {
      return $caption;
    }

    // Collect all possible replacement values from script XML
    $values = [];

    if (isset($xml->receivedMessage)) {
      $values[] = "'".(string) $xml->receivedMessage."'";
    }

    if (isset($xml->formulaList)) {
      foreach (FormulaResolver::resolve($xml->formulaList) as $v) {
        $values[] = (string) $v;
      }
    }

    if (isset($xml->look)) {
      $look_xml = $this->dereferenceXml($xml->look);
      $name = (string) ($look_xml[Constants::NAME_ATTRIBUTE] ?? '');
      if ('' === $name && isset($look_xml->name)) {
        $name = (string) $look_xml->name;
      }
      if ('' !== $name) {
        $values[] = "'".$name."'";
      }
    }

    // Replace _ placeholders left-to-right
    $text = $caption;
    foreach ($values as $value) {
      $pos = strpos($text, '_');
      if (false === $pos) {
        break;
      }
      $text = substr_replace($text, $value, $pos, 1);
    }

    return $text;
  }

  /**
   * Build nested brick list directly from the script's XML, preserving
   * the tree structure of control-flow bricks instead of the parser's
   * flat representation with synthetic end bricks.
   *
   * @return list<array>
   */
  private function buildNestedBrickList(Script $script): array
  {
    // Use reflection to access the script's XML properties for nested parsing
    $reflection = new \ReflectionProperty(Script::class, 'script_xml_properties');
    $xml = $reflection->getValue($script);

    if (!$xml instanceof \SimpleXMLElement || !isset($xml->brickList)) {
      return [];
    }

    return $this->parseBricksFromXml($xml->brickList->children());
  }

  /**
   * Recursively parse bricks from XML, building nested children for control-flow bricks.
   *
   * @return list<array>
   */
  private function parseBricksFromXml(\SimpleXMLElement $bricks_xml): array
  {
    $result = [];

    foreach ($bricks_xml as $brick_xml) {
      $brick_xml = $this->dereferenceXml($brick_xml);
      $type = (string) ($brick_xml[Constants::TYPE_ATTRIBUTE] ?? '');

      // Generate a Brick object to get type, caption, imgFile
      $brick = BrickFactory::generate($brick_xml);
      $formulas = isset($brick_xml->formulaList) ? FormulaResolver::resolve($brick_xml->formulaList) : [];

      $brick_data = [
        'type' => $brick->getType(),
        'category' => $this->resolveCategory($brick->getImgFile()),
        'display_text' => $this->buildDisplayText($brick, $brick_xml, $formulas),
        'commented_out' => $this->isBrickCommentedOut($brick_xml),
        'parameters' => [] === $formulas ? null : array_map(strval(...), $formulas),
        'children' => null,
      ];

      // Build nested children for control-flow bricks
      if (in_array($type, self::LOOP_BRICK_TYPES, true)) {
        $brick_data['children'] = $this->buildLoopChildren($brick_xml);
      } elseif (in_array($type, self::IF_BRICK_TYPES, true)) {
        $brick_data['children'] = $this->buildIfChildren($brick_xml);
      }

      $result[] = $brick_data;
    }

    return $result;
  }

  /**
   * @return array{loop_body: list<array>}
   */
  private function buildLoopChildren(\SimpleXMLElement $brick_xml): array
  {
    $loop_body = [];
    if (property_exists($brick_xml, 'loopBricks') && null !== $brick_xml->loopBricks) {
      $children_xml = $brick_xml->loopBricks->children();
      if (null !== $children_xml) {
        $loop_body = $this->parseBricksFromXml($children_xml);
      }
    }

    return ['loop_body' => $loop_body];
  }

  /**
   * @return array{if_branch: list<array>, else_branch?: list<array>}
   */
  private function buildIfChildren(\SimpleXMLElement $brick_xml): array
  {
    $children = ['if_branch' => []];

    if (property_exists($brick_xml, 'ifBranchBricks') && null !== $brick_xml->ifBranchBricks) {
      $children_xml = $brick_xml->ifBranchBricks->children();
      if (null !== $children_xml) {
        $children['if_branch'] = $this->parseBricksFromXml($children_xml);
      }
    }

    if (property_exists($brick_xml, 'elseBranchBricks') && null !== $brick_xml->elseBranchBricks) {
      $children_xml = $brick_xml->elseBranchBricks->children();
      if (null !== $children_xml) {
        $children['else_branch'] = $this->parseBricksFromXml($children_xml);
      }
    }

    return $children;
  }

  /**
   * @param array<string, string|null> $formulas
   */
  private function buildDisplayText(Brick $brick, \SimpleXMLElement $brick_xml, array $formulas): string
  {
    $caption = $brick->getCaption();
    if (!str_contains($caption, '_')) {
      return $caption;
    }

    $fields = $this->extractNonFormulaFields($brick_xml);
    $formula_values = array_values($formulas);
    $formula_index = 0;

    // Split caption at each _ placeholder and rebuild with resolved values.
    // Use the segment BEFORE each _ to determine which source to pull from.
    $segments = explode('_', $caption);
    $text = $segments[0];

    for ($i = 1, $count = count($segments); $i < $count; ++$i) {
      $segment_lower = strtolower($segments[$i - 1]);
      $value = null;

      if (null !== $fields['userVariable'] && str_contains($segment_lower, 'variable')) {
        $value = "'".$fields['userVariable']."'";
        $fields['userVariable'] = null;
      } elseif (null !== $fields['userList'] && str_contains($segment_lower, 'list')) {
        $value = "'".$fields['userList']."'";
        $fields['userList'] = null;
      } elseif (null !== $fields['look'] && str_contains($segment_lower, 'look')) {
        $value = "'".$fields['look']."'";
        $fields['look'] = null;
      } elseif (null !== $fields['sound'] && str_contains($segment_lower, 'sound')) {
        $value = "'".$fields['sound']."'";
        $fields['sound'] = null;
      } elseif ($formula_index < count($formula_values)) {
        $value = (string) $formula_values[$formula_index];
        ++$formula_index;
      }

      $text .= ($value ?? '_').$segments[$i];
    }

    return $text;
  }

  /**
   * Extract non-formula field values from brick XML (variable names, list names, etc.).
   *
   * @return array{userVariable: string|null, userList: string|null, look: string|null, sound: string|null}
   */
  private function extractNonFormulaFields(\SimpleXMLElement $brick_xml): array
  {
    $fields = ['userVariable' => null, 'userList' => null, 'look' => null, 'sound' => null];

    if (isset($brick_xml->userVariable)) {
      $fields['userVariable'] = $this->resolveXmlName($brick_xml->userVariable);
    }
    if (null === $fields['userVariable'] && isset($brick_xml->userVariableName)) {
      $name = trim((string) $brick_xml->userVariableName);
      if ('' !== $name) {
        $fields['userVariable'] = $name;
      }
    }

    if (isset($brick_xml->userList)) {
      $fields['userList'] = $this->resolveXmlName($brick_xml->userList);
    }
    if (null === $fields['userList'] && isset($brick_xml->userListName)) {
      $name = trim((string) $brick_xml->userListName);
      if ('' !== $name) {
        $fields['userList'] = $name;
      }
    }

    if (isset($brick_xml->look)) {
      $look_xml = $this->dereferenceXml($brick_xml->look);
      $name = trim((string) ($look_xml[Constants::NAME_ATTRIBUTE] ?? ''));
      if ('' === $name) {
        $name = $this->resolveXmlName($brick_xml->look);
      }
      $fields['look'] = ('' !== ($name ?? '')) ? $name : null;
    }

    if (isset($brick_xml->sound)) {
      $fields['sound'] = $this->resolveXmlName($brick_xml->sound);
    }

    return $fields;
  }

  /**
   * Extract a name from an XML element. Handles all Catrobat XML variants:
   * - Direct text: <userVariable>name</userVariable>
   * - Name attribute: <look name="name"/>
   * - Nested serialization: <userVariable serialization="custom"><userVariable><default><name>name</name></default></userVariable></userVariable>
   * - Reference attribute: <userVariable reference="../../..."/>
   */
  private function resolveXmlName(\SimpleXMLElement $xml): ?string
  {
    $resolved = $this->dereferenceXml($xml);

    return $this->extractNameFromXml($resolved, 3);
  }

  private function extractNameFromXml(\SimpleXMLElement $xml, int $depth): ?string
  {
    if ($depth <= 0) {
      return null;
    }

    // Try name attribute
    $name = trim((string) ($xml[Constants::NAME_ATTRIBUTE] ?? ''));
    if ('' !== $name) {
      return $name;
    }

    // Try <default><name>...</name></default> (serialization="custom" format)
    if (isset($xml->default->name)) {
      $name = trim((string) $xml->default->name);
      if ('' !== $name) {
        return $name;
      }
    }

    // Try <name> child element
    if (isset($xml->name)) {
      $name = trim((string) $xml->name);
      if ('' !== $name) {
        return $name;
      }
    }

    // Try direct text content
    $name = trim((string) $xml);
    if ('' !== $name) {
      return $name;
    }

    // Recurse into same-named child (e.g., <userVariable><userVariable><default>...)
    $tag = $xml->getName();
    if (isset($xml->{$tag})) {
      return $this->extractNameFromXml($this->dereferenceXml($xml->{$tag}), $depth - 1);
    }

    return null;
  }

  private function isBrickCommentedOut(\SimpleXMLElement $brick_xml): bool
  {
    if (null !== $brick_xml->commentedOut && 'true' === (string) $brick_xml->commentedOut) {
      return true;
    }

    // Check if parent script is commented out
    $xpath_result = $brick_xml->xpath('../../commentedOut');
    if (null !== $xpath_result && isset($xpath_result[0]) && 'true' === (string) $xpath_result[0]) {
      return true;
    }

    return false;
  }

  private function resolveCategory(string $img_file): string
  {
    return self::IMG_TO_CATEGORY[$img_file] ?? 'unknown';
  }

  private function dereferenceXml(\SimpleXMLElement $xml): \SimpleXMLElement
  {
    if (null !== ($xml[Constants::REFERENCE_ATTRIBUTE] ?? null)) {
      $result = $xml->xpath((string) $xml[Constants::REFERENCE_ATTRIBUTE]);
      if (is_array($result) && isset($result[0])) {
        return $result[0];
      }
    }

    return $xml;
  }
}
