<?php

declare(strict_types=1);

namespace App\Project\Scratch\Converter;

/**
 * Converts a Scratch 3 project into a Catrobat 1.14 archive that Pocket Code opens.
 *
 * Layout and serialization rules: Catroid develop (language 1.14). The stage is 480x360, so Scratch
 * coordinates map 1:1. Pocket Code stores no initial state, so the converter generates it: the background
 * sets up variables and lists, tells every sprite to restore its position, size and costume, and only then
 * fires the green flag (all "when flag clicked" scripts listen for that broadcast).
 */
class ScratchToCatrobatConverter
{
  private const string SCENE = 'Scene';
  private const int MAX_BLOCKS = 100_000;
  private const int MAX_SECONDS = 90;
  private const int MAX_DESCRIPTION_BYTES = 10_000; // Catroweb's upload limit
  private const int MAX_CREDITS_BYTES = 3_000;
  private const int MAX_INITIAL_LIST_ITEMS = 5_000;

  /** Arrow key buttons: position (Scratch stage coordinates), label and WASD twin. */
  private const array ARROWS = [
    'up arrow' => [-174, -92, '▲', 'w'], 'left arrow' => [-218, -136, '◀', 'a'], 'down arrow' => [-174, -136, '▼', 's'], 'right arrow' => [-130, -136, '▶', 'd'],
  ];

  public function __construct(private readonly AssetNormalizer $assets)
  {
  }

  /**
   * @param array<string, mixed>  $project  project.json
   * @param array<string, mixed>  $metadata Scratch API metadata
   * @param array<string, string> $assets   md5ext => bytes
   *
   * @return array<string, int> unsupported Scratch opcodes and how often they occurred
   */
  public function convert(int $scratch_id, array $project, array $metadata, array $assets, ?string $thumbnail, string $archive_path): array
  {
    /** @var list<array<string, mixed>> $targets */
    $targets = array_values(array_filter((array) ($project['targets'] ?? []), is_array(...)));
    $stage = array_values(array_filter($targets, static fn (array $t): bool => (bool) ($t['isStage'] ?? false)))[0] ?? null;
    if (null === $stage) {
      throw new ScratchConversionException('The Scratch project has no stage.');
    }

    $block_count = array_sum(array_map(static fn (array $t): int => \count($t['blocks'] ?? []), $targets));
    if ($block_count > self::MAX_BLOCKS) {
      throw new ScratchConversionException('The Scratch project is too large to convert.');
    }

    $asset_count = array_sum(array_map(static fn (array $t): int => \count($t['costumes'] ?? []) + \count($t['sounds'] ?? []), $targets));
    if ($asset_count > ScratchFetcher::MAX_ASSETS) {
      throw new ScratchConversionException('The Scratch project has too many costumes and sounds to convert.');
    }

    $deadline = microtime(true) + self::MAX_SECONDS;

    $sprites = array_values(array_filter($targets, static fn (array $t): bool => !($t['isStage'] ?? false)));
    usort($sprites, static fn (array $a, array $b): int => ($a['layerOrder'] ?? 0) <=> ($b['layerOrder'] ?? 0));

    $doc = new \DOMDocument('1.0', 'UTF-8');
    $doc->xmlStandalone = true;
    $doc->formatOutput = true;
    $model = new ProjectModel($doc);
    $model->reserveNames($targets);
    $files = [];

    $keys = self::pressedKeys($targets);
    $model->setKeys($keys);
    // An arrow's button also presses its WASD twin, when the project uses both.
    $twins = [];
    foreach (self::ARROWS as $arrow => [, , , $twin]) {
      if (\in_array($arrow, $keys, true) && \in_array($twin, $keys, true)) {
        $twins[$arrow] = $twin;
      }
    }

    $others = 0;
    foreach ($keys as $key) {
      $stage['variables'][$model->helper('key '.$key)] = [$model->helper('key '.$key), 0];
      // ponytail: keys past seven rows of buttons get none, a project with that many keys needs a real keyboard.
      if (!\in_array($key, $twins, true) && (isset(self::ARROWS[$key]) || $others < 28)) {
        $sprites[] = self::keyButton($key, isset($twins[$key]) ? [$key, $twins[$key]] : [$key], $others, $assets);
      }
    }

    $cloned = self::clonedSprites($targets);
    foreach ($targets as $target) {
      if (self::switchesByComputedName($target, 'looks_switchbackdropto', 'BACKDROP') || self::switchesByComputedName($target, 'looks_switchbackdroptoandwait', 'BACKDROP')) {
        $stage['lists'][$model->helper('backdrop names')] = [$model->helper('backdrop names'), array_column($stage['costumes'] ?? [], 'name')];
      }
    }

    $keyed = [ProjectModel::STAGE => $stage];
    $model->addTarget(ProjectModel::STAGE, $stage, 'Background');
    // Pocket Code renames any object called like its localized background, so keep "Background" for the stage only.
    $used = ['background' => true];
    foreach ($sprites as $i => $sprite) {
      $key = 's'.$i;
      if (isset($cloned[(string) $sprite['name']])) {
        foreach (['is clone', 'cloning'] as $purpose) {
          $sprite['variables'][$model->helper($purpose)] = [$model->helper($purpose), 0];
        }
      }

      if (self::touchesMouse($sprite)) {
        $sprite['variables'][$model->helper('finger on me')] = [$model->helper('finger on me'), 0];
      }

      if (self::switchesByComputedName($sprite, 'looks_switchcostumeto', 'COSTUME')) {
        $sprite['lists'][$model->helper('costume names')] = [$model->helper('costume names'), array_column($sprite['costumes'] ?? [], 'name')];
      }

      $name = (string) $sprite['name'];
      for ($n = 2; isset($used[mb_strtolower($name)]); ++$n) {
        $name = $sprite['name'].' '.$n;
      }

      $used[mb_strtolower($name)] = true;
      $keyed[$key] = $sprite;
      $model->addTarget($key, $sprite, $name);
    }

    foreach ($keyed as $key => $target) {
      [$looks, $sounds] = $this->media($target, $assets, $files, $deadline);
      $model->setLooks($key, $looks);
      $model->setSounds($key, $sounds);
    }

    $converter = new BlockConverter($doc, $model, $deadline);
    $scripts = [];
    $definitions = [];
    foreach ($keyed as $key => $target) {
      $scripts[$key] = $converter->convertTarget($target, $key);
      $definitions[$key] = $converter->procedureDefinitions();
    }

    // Generated helpers come after the conversion, which is what asks for them.
    foreach ($keyed as $key => $target) {
      $scripts[$key] = [...$this->helperScripts($converter, $model, $key, $target, $stage), ...$scripts[$key]];
    }

    $program = $doc->appendChild($doc->createElement('program'));
    $program->appendChild($this->header($doc, $scratch_id, $metadata, $model));
    $program->appendChild($doc->createElement('settings'));
    $scene = $program->appendChild($doc->createElement('scenes'))->appendChild($doc->createElement('scene'));
    $scene->appendChild($doc->createElement('name', self::SCENE));
    $objects = $scene->appendChild($doc->createElement('objectList'));
    foreach ($keyed as $key => $target) {
      $objects->appendChild($this->object($doc, $converter, $model, $key, $target, $scripts[$key], $definitions[$key]));
    }

    $variables = $program->appendChild($doc->createElement('programVariableList'));
    foreach ($model->programVariableNames() as $name) {
      $variables->appendChild($model->variableElement(null, $name));
    }

    $lists = $program->appendChild($doc->createElement('programListOfLists'));
    foreach ($stage['lists'] ?? [] as $id => $list) {
      $lists->appendChild($model->listElement(ProjectModel::STAGE, (string) $list[0], (string) $id));
    }

    $program->appendChild($doc->createElement('programMultiplayerVariableList'));

    XStreamReferences::resolve($doc);

    // Scratch allows control characters in names and texts; XML 1.0 does not, and Pocket Code would reject the file.
    $xml = (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string) $doc->saveXML());
    $this->writeArchive($archive_path, $xml, $files, $thumbnail);

    return $model->unsupportedOpcodes();
  }

  /**
   * @param array<string, mixed>  $target
   * @param array<string, string> $assets   md5ext => bytes
   * @param array<string, string> $files    archive path => bytes, filled here
   * @param float                 $deadline microtime(true) after which the conversion gives up
   *
   * @return array{0: list<array{name: string, file: string}>, 1: list<array{name: string, file: string}>}
   */
  private function media(array $target, array $assets, array &$files, float $deadline): array
  {
    $looks = [];
    foreach ($target['costumes'] ?? [] as $costume) {
      $png = $this->assets->costumeToPng($assets[(string) $costume['md5ext']] ?? throw new ScratchConversionException('Missing costume.'), $costume, $deadline);
      $file = self::uniqueFile($files, self::SCENE.'/images/', (string) $costume['name'], (string) $costume['assetId'], 'png');
      $files[self::SCENE.'/images/'.$file] = $png;
      $looks[] = ['name' => (string) $costume['name'], 'file' => $file];
    }

    $sounds = [];
    foreach ($target['sounds'] ?? [] as $sound) {
      [$bytes, $extension] = $this->assets->playableSound($assets[(string) $sound['md5ext']] ?? throw new ScratchConversionException('Missing sound.'), $sound, $deadline);
      $file = self::uniqueFile($files, self::SCENE.'/sounds/', (string) $sound['name'], (string) $sound['assetId'], $extension);
      $files[self::SCENE.'/sounds/'.$file] = $bytes;
      $sounds[] = ['name' => (string) $sound['name'], 'file' => $file];
    }

    return [$looks, $sounds];
  }

  /**
   * @param array<string, mixed> $target
   * @param array<string, mixed> $stage
   *
   * @return list<\DOMElement>
   */
  private function helperScripts(BlockConverter $c, ProjectModel $model, string $key, array $target, array $stage): array
  {
    $scripts = [];
    if ($model->isStage($key)) {
      $init = $this->initialData($c, $model, null, $stage);
      $current = (int) ($stage['currentCostume'] ?? 0);
      if ($current > 0 && $current < $model->lookCount($key)) {
        $init[] = $c->brick('SetBackgroundBrick', [], [], [$model->lookElement($key, $current)]);
      }

      $init[] = $c->brick('BroadcastWaitBrick', [], [], [$c->text('broadcastMessage', $model->helper('init sprites'))]);
      $init[] = $c->brick('BroadcastBrick', [], [], [$c->text('broadcastMessage', $model->helper('green flag'))]);
      $scripts[] = $c->script('StartScript', $init);
      $scripts[] = $c->script('BroadcastScript', [$c->brick('NextLookBrick')], [$c->text('receivedMessage', $model->helper('next backdrop'))]);
      $scripts[] = $c->script('BroadcastScript', [$c->brick('PreviousLookBrick')], [$c->text('receivedMessage', $model->helper('previous backdrop'))]);
    } else {
      $style = ['left-right' => '0', 'all around' => '1', "don't rotate" => '2'][(string) ($target['rotationStyle'] ?? 'all around')] ?? '1';
      $init = [
        $c->brick('PlaceAtBrick', ['X_POSITION' => Formula::number((float) ($target['x'] ?? 0)), 'Y_POSITION' => Formula::number((float) ($target['y'] ?? 0))]),
        $c->brick('SetSizeToBrick', ['SIZE' => Formula::number((float) ($target['size'] ?? 100))]),
        $c->brick('PointInDirectionBrick', ['DEGREES' => Formula::number((float) ($target['direction'] ?? 90))]),
        $c->brick('SetRotationStyleBrick', [], [], [$c->text('selection', $style)]),
      ];
      $current = (int) ($target['currentCostume'] ?? 0);
      if ($current < $model->lookCount($key)) {
        $init[] = $c->brick('SetLookBrick', [], [], [$model->lookElement($key, $current)]);
      }

      $init[] = $c->brick(false === ($target['visible'] ?? true) ? 'HideBrick' : 'ShowBrick');
      $init = [...$init, ...$this->initialData($c, $model, $key, $target)];
      $scripts[] = $c->script('BroadcastScript', $init, [$c->text('receivedMessage', $model->helper('init sprites'))]);

      if (isset($target['scratchKeys'])) {
        $scripts = [...$scripts, ...$this->keyScripts($c, $model, $target['scratchKeys'])];
      }

      if (self::touchesMouse($target)) {
        // Scratch's mouse stays where it was released: buttons that click on release ask "touching mouse-pointer?"
        // after the finger is gone, when Pocket Code's "touches finger" is already false. Remember where the sprite
        // was when the last touch hit it; BlockConverter::touching() counts that until the sprite moves.
        // ponytail: a costume or size change after the release still counts as touching.
        $remember = fn (): \DOMElement => $c->brick('ForeverBrick', [], [$c->list('loopBricks', [
          $c->brick('IfThenLogicBeginBrick', ['IF_CONDITION' => Formula::sensor('FINGER_TOUCHED')], [$c->list('ifBranchBricks', [
            $c->brick('SetVariableBrick', [
              'VARIABLE' => Formula::fn('IF_THEN_ELSE', Formula::sensor('COLLIDES_WITH_FINGER'), BlockConverter::position(), Formula::string('')),
            ], [], [$model->variableElement($key, $model->helper('finger on me'))]),
          ])]),
        ])]);
        $scripts = [...$scripts, ...$this->helperLoop($c, $model, $remember)];
      }

      if ($model->receivesCloneRequests($key)) {
        $scripts = [...$scripts, ...$this->cloneRequestScripts($c, $model, $key)];
      }
    }

    $copies = [];
    foreach ($model->shadows() as $name => $shadow) {
      if ($shadow['sprite'] === $key) {
        $copies[] = $c->brick('SetVariableBrick', ['VARIABLE' => $shadow['formula']], [], [$model->variableElement(null, $name)]);
      }
    }

    if ([] !== $copies) {
      // ponytail: "stop other scripts in sprite" stops this for good; a restart would need clones to stay out of it.
      $scripts[] = $c->script('StartScript', [$c->brick('ForeverBrick', [], [$c->list('loopBricks', $copies)])]);
    }

    return $scripts;
  }

  /**
   * A generated loop that runs in the sprite and each of its clones, and restarts after "stop other scripts in sprite".
   *
   * @param \Closure(): \DOMElement $loop
   *
   * @return list<\DOMElement>
   */
  private function helperLoop(BlockConverter $c, ProjectModel $model, \Closure $loop): array
  {
    return [
      $c->script('StartScript', [$loop()]),
      $c->script('WhenClonedScript', [$loop()]),
      $c->script('BroadcastScript', [$loop()], [$c->text('receivedMessage', $model->helper('restart helpers'))]),
    ];
  }

  /**
   * The original sprite clones itself once per counted request ("create clone of <sprite>" from elsewhere).
   *
   * Catroid runs one brick of a script per frame, and a new request broadcast restarts this script between frames,
   * so "clone" and "count it" can't be two steps of the handler: a restart between them would clone twice. The
   * clone counts itself instead. "cloning" is 1 only around the CloneBrick, the clone inherits it with the other
   * variables, and its first frame subtracts it from the counter; clones are placed before their original on
   * stage, so they act first in the next frame, before the handler checks the counter again.
   * ponytail: another "create clone of myself" by the original in that one frame also counts as a request.
   *
   * @return list<\DOMElement>
   */
  private function cloneRequestScripts(BlockConverter $c, ProjectModel $model, string $key): array
  {
    $request = $model->cloneRequestNames($key);
    $cloning = fn (): \DOMElement => $model->variableElement($key, $model->helper('cloning'));
    $set_cloning = fn (int $value): \DOMElement => $c->brick('SetVariableBrick', ['VARIABLE' => Formula::number($value)], [], [$cloning()]);
    // Clones hear the broadcast too; only the original may answer it, as in Scratch.
    $is_original = Formula::op('EQUAL', Formula::variable($model->helper('is clone')), Formula::number(0));

    return [
      $c->script('BroadcastScript', [
        $c->brick('IfThenLogicBeginBrick', ['IF_CONDITION' => $is_original], [$c->list('ifBranchBricks', [
          $set_cloning(0),
          $c->brick('RepeatUntilBrick', ['REPEAT_UNTIL_CONDITION' => Formula::op('SMALLER_THAN', Formula::variable($request['counter']), Formula::number(1))], [$c->list('loopBricks', [
            $set_cloning(1),
            $c->brick('CloneBrick'),
            $set_cloning(0),
          ])]),
        ])]),
      ], [$c->text('receivedMessage', $request['message'])]),
      // Separate scripts, so all three run in the clone's first frame, before its own scripts can clone again.
      $c->script('WhenClonedScript', [
        $c->brick('ChangeVariableBrick', ['VARIABLE_CHANGE' => Formula::unary('MINUS', Formula::variable($model->helper('cloning')))], [], [$model->variableElement(null, $request['counter'])]),
      ]),
      $c->script('WhenClonedScript', [$set_cloning(0)]),
      $c->script('WhenClonedScript', [
        $c->brick('SetVariableBrick', ['VARIABLE' => Formula::number(1)], [], [$model->variableElement($key, $model->helper('is clone'))]),
      ]),
    ];
  }

  /**
   * Pocket Code has no keyboard. Holding a key's button sets the keys' variables, which "key pressed?" reads, and
   * broadcasts them like a keyboard does: once on the tap, which catches even taps shorter than a frame, then
   * repeated after half a second.
   * The buttons come to the front every frame, above sprites that "go to front".
   * ponytail: touching a button also counts as "mouse down".
   *
   * @param list<string> $keys the button's key and its twin
   *
   * @return list<\DOMElement>
   */
  private function keyScripts(BlockConverter $c, ProjectModel $model, array $keys): array
  {
    $names = array_map(static fn (string $key): string => $model->helper('key '.$key), $keys);
    $pressed = Formula::variable($names[0]);
    $send = fn (): array => array_map(
      static fn (string $name): \DOMElement => $c->brick('BroadcastBrick', [], [], [$c->text('broadcastMessage', $name)]),
      [...$names, $model->helper('key any')],
    );

    return [
      $c->script('WhenScript', $send()),
      $c->script('StartScript', [$c->brick('ForeverBrick', [], [$c->list('loopBricks', [
        $c->brick('ComeToFrontBrick'),
        ...array_map(
          static fn (string $name): \DOMElement => $c->brick('SetVariableBrick', ['VARIABLE' => Formula::sensor('COLLIDES_WITH_FINGER')], [], [$model->variableElement(null, $name)]),
          $names,
        ),
      ])])]),
      $c->script('StartScript', [$c->brick('ForeverBrick', [], [$c->list('loopBricks', [
        $c->brick('WaitUntilBrick', ['IF_CONDITION' => $pressed]),
        $c->brick('WaitBrick', ['TIME_TO_WAIT_IN_SECONDS' => Formula::number(0.5)]),
        $c->brick('RepeatUntilBrick', ['REPEAT_UNTIL_CONDITION' => Formula::unary('LOGICAL_NOT', $pressed)], [$c->list('loopBricks', [
          ...$send(),
          $c->brick('WaitBrick', ['TIME_TO_WAIT_IN_SECONDS' => Formula::number(0.05)]),
        ])]),
      ])])]),
    ];
  }

  /**
   * Bricks that give a target's variables and lists their saved Scratch values; null sprite = program-wide.
   *
   * @param array<string, mixed> $target
   *
   * @return list<\DOMElement>
   */
  private function initialData(BlockConverter $c, ProjectModel $model, ?string $key, array $target): array
  {
    $bricks = [];
    foreach ($target['variables'] ?? [] as $id => $variable) {
      $bricks[] = $c->brick('SetVariableBrick', ['VARIABLE' => self::saved($variable[1] ?? 0)], [], [$model->variableElement($key, (string) $variable[0], (string) $id)]);
    }

    $items = 0;
    foreach ($target['lists'] ?? [] as $id => $list) {
      $element = fn (): \DOMElement => $model->listElement($key ?? ProjectModel::STAGE, (string) $list[0], (string) $id);
      $bricks[] = $c->brick('ClearUserListBrick', [], [], [$element()], formula_list: true);
      foreach ((array) ($list[1] ?? []) as $item) {
        if (++$items > self::MAX_INITIAL_LIST_ITEMS) {
          $bricks[] = $c->note('the remaining saved list items were left out');
          break 2;
        }

        $bricks[] = $c->brick('AddItemToUserListBrick', ['LIST_ADD_ITEM' => self::saved($item)], [], [$element()]);
      }
    }

    return $bricks;
  }

  /** A saved Scratch value: numbers stay numbers, strings stay strings ("001" is not 1). */
  private static function saved(mixed $value): Formula
  {
    return \is_string($value) ? Formula::string($value) : Formula::literal($value);
  }

  /**
   * @param array<string, mixed> $target
   * @param list<\DOMElement>    $scripts
   * @param list<\DOMElement>    $definitions
   */
  private function object(\DOMDocument $doc, BlockConverter $c, ProjectModel $model, string $key, array $target, array $scripts, array $definitions): \DOMElement
  {
    $object = $doc->createElement('object');
    $object->setAttribute('type', 'Sprite');
    $object->setAttribute('name', $model->objectName($key));

    $looks = $object->appendChild($doc->createElement('lookList'));
    for ($i = 0; $i < $model->lookCount($key); ++$i) {
      $looks->appendChild($model->lookElement($key, $i));
    }

    $sounds = $object->appendChild($doc->createElement('soundList'));
    for ($i = 0; $i < $model->soundCount($key); ++$i) {
      $sounds->appendChild($model->soundElement($key, $i));
    }

    $object->appendChild($c->list('scriptList', $scripts));
    $object->appendChild($doc->createElement('nfcTagList'));

    $variables = $object->appendChild($doc->createElement('userVariables'));
    $lists = $object->appendChild($doc->createElement('userLists'));
    if (!$model->isStage($key)) {
      foreach ($target['variables'] ?? [] as $id => $variable) {
        $variables->appendChild($model->variableElement($key, (string) $variable[0], (string) $id));
      }

      foreach ($target['lists'] ?? [] as $id => $list) {
        $lists->appendChild($model->listElement($key, (string) $list[0], (string) $id));
      }
    }

    $object->appendChild($c->list('userDefinedBrickList', $definitions));

    return $object;
  }

  /** @param array<string, mixed> $metadata */
  private function header(\DOMDocument $doc, int $scratch_id, array $metadata, ProjectModel $model): \DOMElement
  {
    $author = (string) ($metadata['author']['username'] ?? '');
    $description = trim(implode("\n\n", array_filter([(string) ($metadata['instructions'] ?? ''), (string) ($metadata['description'] ?? '')])));
    $credits = 'Converted from Scratch project https://scratch.mit.edu/projects/'.$scratch_id.('' !== $author ? ' by '.$author : '').'.';
    $unsupported = array_sum($model->unsupportedOpcodes());
    if ($unsupported > 0) {
      $credits .= ' '.$unsupported.' Scratch blocks have no Pocket Code equivalent and are marked with notes.';
    }

    $fields = [
      'applicationBuildName' => '', 'applicationBuildNumber' => '0', 'applicationBuildType' => 'signedRelease',
      'applicationName' => 'Pocket Code', 'applicationVersion' => '1.4.1', 'catrobatLanguageVersion' => '1.14',
      'dateTimeUpload' => '', 'description' => mb_strcut($description, 0, self::MAX_DESCRIPTION_BYTES), 'deviceName' => 'Scratch converter',
      'isCastProject' => 'false', 'landscapeMode' => 'true', 'listeningLanguageTag' => '',
      'mediaLicense' => 'https://developer.catrobat.org/ccbysa_v4', 'notesAndCredits' => mb_strcut($credits, 0, self::MAX_CREDITS_BYTES),
      'platform' => 'Android', 'platformVersion' => '33', 'programLicense' => 'https://developer.catrobat.org/agpl_v3',
      'programName' => '' !== trim((string) ($metadata['title'] ?? '')) ? (string) $metadata['title'] : 'Scratch project '.$scratch_id,
      'remixOf' => 'https://scratch.mit.edu/projects/'.$scratch_id, 'scenesEnabled' => 'true',
      'screenHeight' => '360', 'screenMode' => 'MAXIMIZE', 'screenWidth' => '480',
      'tags' => '', 'url' => '', 'userHandle' => $author,
    ];

    $header = $doc->createElement('header');
    foreach ($fields as $name => $value) {
      $header->appendChild($doc->createElement($name))->appendChild($doc->createTextNode($value));
    }

    return $header;
  }

  /** @param array<string, string> $files */
  private function writeArchive(string $path, string $xml, array $files, ?string $thumbnail): void
  {
    $zip = new \ZipArchive();
    if (true !== $zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
      throw new ScratchConversionException('Could not write the converted project.');
    }

    $zip->addFromString('code.xml', $xml);
    foreach ($files as $name => $bytes) {
      $zip->addFromString($name, $bytes);
    }

    if (null !== $thumbnail) {
      $zip->addFromString('automatic_screenshot.png', $thumbnail);
      $zip->addFromString(self::SCENE.'/automatic_screenshot.png', $thumbnail);
    }

    $zip->close();
  }

  /**
   * Keys the project reacts to, arrows first, then space; only "any key" gets the space bar.
   *
   * @param list<array<string, mixed>> $targets
   *
   * @return list<string>
   */
  private static function pressedKeys(array $targets): array
  {
    $keys = [];
    foreach ($targets as $target) {
      foreach ($target['blocks'] ?? [] as $block) {
        if (\is_array($block) && \in_array($block['opcode'] ?? null, ['event_whenkeypressed', 'sensing_keyoptions'], true)) {
          $keys[mb_strtolower((string) ($block['fields']['KEY_OPTION'][0] ?? ''))] = true;
        }
      }
    }

    if (isset($keys['any']) && 1 === \count($keys)) {
      $keys['space'] = true;
    }

    unset($keys['any'], $keys['']);
    $keys = array_keys($keys);
    $rank = static fn (string $key): array => [isset(self::ARROWS[$key]) ? 0 : ('space' === $key ? 1 : 2), $key];
    usort($keys, static fn (string $a, string $b): int => $rank($a) <=> $rank($b));

    return array_map(strval(...), $keys);
  }

  /**
   * A button sprite for a key: arrows as a cross bottom left, the other keys in rows of four slots bottom right,
   * space first and two slots wide.
   *
   * @param list<string>          $pressed keys the button presses
   * @param array<string, string> $assets  md5ext => bytes, the button's costume is added
   *
   * @return array<string, mixed>
   */
  private static function keyButton(string $key, array $pressed, int &$others, array &$assets): array
  {
    $width = 'space' === $key ? 88 : 40;
    if (isset(self::ARROWS[$key])) {
      [$x, $y, $label] = self::ARROWS[$key];
      $label .= isset($pressed[1]) ? ' '.mb_strtoupper($pressed[1]) : '';
    } else {
      $label = 'space' === $key ? 'space' : mb_strtoupper($key);
      [$x, $y] = [216 - ($others % 4) * 48 - ('space' === $key ? 24 : 0), -136 + intdiv($others, 4) * 48];
      $others += 'space' === $key ? 2 : 1;
    }

    $svg = sprintf(
      '<svg xmlns="http://www.w3.org/2000/svg" width="%1$d" height="40"><rect x="1" y="1" width="%2$d" height="38" rx="8" fill="#fff" fill-opacity="0.6" stroke="#333" stroke-opacity="0.8" stroke-width="2"/>'
      .'<text x="%3$d" y="27" font-family="DejaVu Sans, sans-serif" font-size="%4$d" text-anchor="middle" fill="#333">%5$s</text></svg>',
      $width, $width - 2, $width / 2, mb_strlen($label) > 2 ? 16 : 20, htmlspecialchars($label, ENT_XML1),
    );
    $md5 = md5($svg);
    $assets[$md5.'.svg'] = $svg;

    return [
      'name' => '⌨ '.$key, 'scratchKeys' => $pressed, 'x' => $x, 'y' => $y, 'rotationStyle' => "don't rotate",
      'costumes' => [['name' => $key, 'md5ext' => $md5.'.svg', 'assetId' => $md5, 'dataFormat' => 'svg', 'rotationCenterX' => $width / 2, 'rotationCenterY' => 20]],
    ];
  }

  /**
   * Names of sprites that another sprite clones ("create clone of <sprite>").
   *
   * @param list<array<string, mixed>> $targets
   *
   * @return array<string, true>
   */
  private static function clonedSprites(array $targets): array
  {
    $names = [];
    foreach ($targets as $target) {
      foreach ($target['blocks'] ?? [] as $block) {
        $option = \is_array($block) && 'control_create_clone_of_menu' === ($block['opcode'] ?? null) ? ($block['fields']['CLONE_OPTION'][0] ?? null) : null;
        if (\is_string($option) && '_myself_' !== $option) {
          $names[$option] = true;
        }
      }
    }

    return $names;
  }

  /** @param array<string, mixed> $target */
  private static function touchesMouse(array $target): bool
  {
    foreach ($target['blocks'] ?? [] as $block) {
      if (\is_array($block) && '_mouse_' === ($block['fields']['TOUCHINGOBJECTMENU'][0] ?? null)) {
        return true;
      }
    }

    return false;
  }

  /**
   * Whether a target switches costume/backdrop to a computed value (a reporter dropped into the menu).
   *
   * @param array<string, mixed> $target
   */
  private static function switchesByComputedName(array $target, string $opcode, string $input): bool
  {
    $blocks = $target['blocks'] ?? [];
    foreach ($blocks as $block) {
      if (!\is_array($block) || $opcode !== ($block['opcode'] ?? null)) {
        continue;
      }

      $value = $block['inputs'][$input][1] ?? null;
      if (!\is_string($value) || !($blocks[$value]['shadow'] ?? false)) {
        return true;
      }
    }

    return false;
  }

  /** @param array<string, string> $files */
  private static function uniqueFile(array $files, string $folder, string $name, string $asset_id, string $extension): string
  {
    $base = trim((string) preg_replace('/[^\p{L}\p{N} _.-]+/u', '_', $name), ' .') ?: 'file';
    $base = mb_substr($base, 0, 60).'_'.substr(preg_replace('/[^0-9a-f]/', '', $asset_id) ?: md5($name), 0, 8);
    $file = $base.'.'.$extension;
    for ($n = 2; isset($files[$folder.$file]); ++$n) {
      $file = $base.'_'.$n.'.'.$extension;
    }

    return $file;
  }
}
