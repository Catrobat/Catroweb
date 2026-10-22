<?php

declare(strict_types=1);

namespace App\Project\Scratch\Converter;

/**
 * Translates the scripts of one Scratch target into Catrobat 1.14 scripts and bricks.
 *
 * Mapping and its semantic differences follow Catroid develop (language 1.14); see
 * docs/architecture/adr-scratch-converter-evaluation.md. Blocks without an equivalent become NoteBricks, so a
 * converted project still loads and shows what was dropped.
 */
final class BlockConverter
{
  /**
   * Untrusted block graphs: bound nesting (each level recurses) and total output. Reporters can be shared, so
   * output grows with every use of a block, not with the number of blocks; count bricks and formula nodes.
   */
  private const int MAX_DEPTH = 200;
  private const int MAX_NODES = 200_000;
  // Text multiplies with sharing too: one 8 KiB string behind 15 levels of shared reporters is 256 MiB of XML.
  private const int MAX_TEXT_BYTES = 16 * 1024 * 1024;

  /** @var array<string, array<string, mixed>> */
  private array $blocks = [];

  private string $sprite_key = '';

  /** @var array<string, array{id: string, names: list<string>, ids: list<string>, labels: list<array{0: string, 1: string}>, warp: bool}> */
  private array $procedures = [];

  /** @var list<string>|null argument names while converting a procedure body */
  private ?array $arguments = null;

  /** @var array<string, true> blocks being converted right now, to reject cycles */
  private array $active = [];

  private int $nodes = 0;

  private int $text_bytes = 0;

  public function __construct(
    private readonly \DOMDocument $doc,
    private readonly ProjectModel $model,
    private readonly float $deadline,
  ) {
  }

  /**
   * @param array<string, mixed> $target
   *
   * @return list<\DOMElement> scripts
   */
  public function convertTarget(array $target, string $sprite_key): array
  {
    $this->blocks = array_filter($target['blocks'] ?? [], is_array(...));
    $this->sprite_key = $sprite_key;
    $this->procedures = $this->collectProcedures();

    $scripts = [];
    foreach ($this->blocks as $block) {
      if (!($block['topLevel'] ?? false) || !isset($block['opcode'])) {
        continue;
      }

      $script = $this->convertHat($block);
      if (null !== $script) {
        $scripts[] = $script;
      }
    }

    return $scripts;
  }

  /** @return list<\DOMElement> userDefinedBrickList entries for the procedures of the last converted target */
  public function procedureDefinitions(): array
  {
    $definitions = [];
    foreach ($this->procedures as $proccode => $procedure) {
      $definitions[] = $this->brick('UserDefinedBrick', [], [], [
        $this->text('isCallingBrick', 'false'),
        $this->dataList($proccode),
        $this->text('userDefinedBrickID', $procedure['id']),
      ], formula_list: true);
    }

    return $definitions;
  }

  // ---- scripts ----

  /** @param array<string, mixed> $hat */
  private function convertHat(array $hat): ?\DOMElement
  {
    $body = fn (): array => $this->stack($hat['next'] ?? null);

    switch ($hat['opcode']) {
      case 'event_whenflagclicked':
        return $this->script('BroadcastScript', $body(), [$this->text('receivedMessage', $this->model->helper('green flag'))]);
      case 'event_whenthisspriteclicked':
      case 'event_whenstageclicked':
        return $this->script('WhenScript', $body());
      case 'event_whenbroadcastreceived':
        return $this->script('BroadcastScript', $body(), [$this->text('receivedMessage', $this->field($hat, 'BROADCAST_OPTION'))]);
      case 'control_start_as_clone':
        return $this->script('WhenClonedScript', $body());
      case 'event_whenbackdropswitchesto':
        $look = $this->model->backdropLook($this->field($hat, 'BACKDROP'));

        return null === $look ? null : $this->script('WhenBackgroundChangesScript', $body(), [$look]);
      case 'event_whengreaterthan':
        $sensor = 'timer' === strtolower($this->field($hat, 'WHENGREATERTHANMENU')) ? 'TIMER' : 'LOUDNESS';

        return $this->conditionScript(Formula::op('GREATER_THAN', Formula::sensor($sensor), $this->number($hat, 'VALUE')), $body());
      case 'event_whentouchingobject':
        return $this->conditionScript($this->touching($this->menu($hat, 'TOUCHINGOBJECTMENU')), $body());
      case 'event_whenkeypressed':
        // The on-screen key buttons broadcast this while pressed.
        return $this->script('BroadcastScript', $body(), [$this->text('receivedMessage', $this->model->helper('key '.mb_strtolower($this->field($hat, 'KEY_OPTION'))))]);
      case 'procedures_definition':
        return $this->procedureScript($hat, $body);
      default:
        return null; // loose blocks and reporters lying around in the editor
    }
  }

  /**
   * @param list<\DOMElement> $bricks
   * @param list<\DOMElement> $extra  fields after scriptId
   */
  public function script(string $type, array $bricks, array $extra = []): \DOMElement
  {
    $script = $this->doc->createElement('script');
    $script->setAttribute('type', $type);
    $script->setAttribute('posX', '0.0');
    $script->setAttribute('posY', '0.0');
    $script->appendChild($this->list('brickList', $bricks));
    $script->appendChild($this->text('commentedOut', 'false'));
    $script->appendChild($this->text('scriptId', self::uuid()));
    foreach ($extra as $element) {
      $script->appendChild($element);
    }

    return $script;
  }

  /** @param list<\DOMElement> $bricks */
  private function conditionScript(Formula $condition, array $bricks): \DOMElement
  {
    $map = $this->doc->createElement('formulaMap');
    $map->appendChild($condition->toElement($this->doc, 'formula', ['category' => 'IF_CONDITION']));

    return $this->script('WhenConditionScript', $bricks, [$map]);
  }

  /** @param array<string, mixed> $hat */
  private function procedureScript(array $hat, \Closure $body): ?\DOMElement
  {
    $prototype = $this->blocks[$this->inputBlockId($hat, 'custom_block') ?? ''] ?? null;
    $procedure = $this->procedures[$prototype['mutation']['proccode'] ?? ''] ?? null;
    if (null === $procedure) {
      return null;
    }

    $this->arguments = $procedure['names'];
    $bricks = $body();
    $this->arguments = null;

    return $this->script('UserDefinedScript', $bricks, [
      $this->text('screenRefresh', $procedure['warp'] ? 'false' : 'true'),
      $this->text('userDefinedBrickID', $procedure['id']),
      $this->list('userDefinedBrickInputs', []),
    ]);
  }

  // ---- bricks ----

  /** @return list<\DOMElement> */
  private function stack(mixed $id): array
  {
    $bricks = [];
    $seen = [];
    while (\is_string($id) && isset($this->blocks[$id])) {
      if (isset($seen[$id])) {
        throw new ScratchConversionException('The Scratch project contains a script that runs into itself.');
      }

      $seen[$id] = true;
      foreach ($this->guarded($id, fn (): array => $this->convertBlock($this->blocks[$id])) as $brick) {
        $bricks[] = $brick;
      }

      $id = $this->blocks[$id]['next'] ?? null;
    }

    return $bricks;
  }

  /**
   * Runs $convert for block $id, rejecting cycles, too deep nesting and too much output.
   *
   * @template T
   *
   * @param \Closure(): T $convert
   *
   * @return T
   */
  private function guarded(string $id, \Closure $convert): mixed
  {
    if (isset($this->active[$id])) {
      throw new ScratchConversionException('The Scratch project contains a block that contains itself.');
    }

    if (\count($this->active) >= self::MAX_DEPTH) {
      throw new ScratchConversionException('The Scratch project is too large or too deeply nested to convert.');
    }

    $this->spend(self::textBytes($this->blocks[$id] ?? []));
    $this->active[$id] = true;
    try {
      return $convert();
    } finally {
      unset($this->active[$id]);
    }
  }

  /** @param int $text_bytes text this step writes into the project: its fields, literals and messages */
  private function spend(int $text_bytes = 0): void
  {
    $this->text_bytes += $text_bytes;
    if (++$this->nodes > self::MAX_NODES || $this->text_bytes > self::MAX_TEXT_BYTES || (0 === $this->nodes % 1024 && microtime(true) > $this->deadline)) {
      throw new ScratchConversionException('The Scratch project is too large or too deeply nested to convert.');
    }
  }

  /**
   * @param array<string, mixed> $b
   *
   * @return list<\DOMElement>
   */
  private function convertBlock(array $b): array
  {
    $opcode = (string) $b['opcode'];
    $sub = fn (string $name = 'SUBSTACK'): array => $this->stack($this->inputBlockId($b, $name));

    switch ($opcode) {
      // motion
      case 'motion_movesteps': return [$this->brick('MoveNStepsBrick', ['STEPS' => $this->number($b, 'STEPS')])];
      case 'motion_turnright': return [$this->brick('TurnRightBrick', ['TURN_RIGHT_DEGREES' => $this->number($b, 'DEGREES')])];
      case 'motion_turnleft': return [$this->brick('TurnLeftBrick', ['TURN_LEFT_DEGREES' => $this->number($b, 'DEGREES')])];
      case 'motion_gotoxy': return [$this->brick('PlaceAtBrick', ['X_POSITION' => $this->number($b, 'X'), 'Y_POSITION' => $this->number($b, 'Y')])];
      case 'motion_goto':
        [$x, $y] = $this->targetPosition($this->menu($b, 'TO'));

        return [$this->brick('PlaceAtBrick', ['X_POSITION' => $x, 'Y_POSITION' => $y])];
      case 'motion_glidesecstoxy':
        return [$this->brick('GlideToBrick', ['DURATION_IN_SECONDS' => $this->number($b, 'SECS'), 'X_DESTINATION' => $this->number($b, 'X'), 'Y_DESTINATION' => $this->number($b, 'Y')])];
      case 'motion_glideto':
        [$x, $y] = $this->targetPosition($this->menu($b, 'TO'));

        return [$this->brick('GlideToBrick', ['DURATION_IN_SECONDS' => $this->number($b, 'SECS'), 'X_DESTINATION' => $x, 'Y_DESTINATION' => $y])];
      case 'motion_pointindirection': return [$this->brick('PointInDirectionBrick', ['DEGREES' => $this->number($b, 'DIRECTION')])];
      case 'motion_pointtowards':
        $towards = $this->menu($b, 'TOWARDS');
        if ('_random_' === $towards) {
          return [$this->brick('PointInDirectionBrick', ['DEGREES' => Formula::fn('RAND', Formula::number(-180), Formula::number(180))])];
        }

        [$x, $y] = $this->targetPosition($towards);

        return [$this->brick('PointInDirectionBrick', ['DEGREES' => Formula::fn('ARCTAN2',
          Formula::op('MINUS', $x, Formula::sensor('OBJECT_X')),
          Formula::op('MINUS', $y, Formula::sensor('OBJECT_Y')))])];
      case 'motion_changexby': return [$this->brick('ChangeXByNBrick', ['X_POSITION_CHANGE' => $this->number($b, 'DX')])];
      case 'motion_setx': return [$this->brick('SetXBrick', ['X_POSITION' => $this->number($b, 'X')])];
      case 'motion_changeyby': return [$this->brick('ChangeYByNBrick', ['Y_POSITION_CHANGE' => $this->number($b, 'DY')])];
      case 'motion_sety': return [$this->brick('SetYBrick', ['Y_POSITION' => $this->number($b, 'Y')])];
      case 'motion_ifonedgebounce': return [$this->brick('IfOnEdgeBounceBrick')];
      case 'motion_setrotationstyle':
        $style = ['left-right' => '0', 'all around' => '1', "don't rotate" => '2'][strtolower($this->field($b, 'STYLE'))] ?? '1';

        return [$this->brick('SetRotationStyleBrick', [], [], [$this->text('selection', $style)])];

        // looks
      case 'looks_say': return [$this->brick('SayBubbleBrick', ['STRING' => $this->textInput($b, 'MESSAGE')])];
      case 'looks_sayforsecs': return [$this->brick('SayForBubbleBrick', ['STRING' => $this->textInput($b, 'MESSAGE'), 'DURATION_IN_SECONDS' => $this->number($b, 'SECS')])];
      case 'looks_think': return [$this->brick('ThinkBubbleBrick', ['STRING' => $this->textInput($b, 'MESSAGE')])];
      case 'looks_thinkforsecs': return [$this->brick('ThinkForBubbleBrick', ['STRING' => $this->textInput($b, 'MESSAGE'), 'DURATION_IN_SECONDS' => $this->number($b, 'SECS')])];
      case 'looks_switchcostumeto':
        $name = $this->menu($b, 'COSTUME');
        $look = null === $name ? null : $this->model->look($this->sprite_key, $name);
        if (null !== $look) {
          return [$this->brick('SetLookBrick', [], [], [$look])];
        }

        // A name that is no costume: Scratch reads it as a number, or does nothing.
        if (null !== $name) {
          return match (true) {
            is_numeric($name) => [$this->brick('SetLookByIndexBrick', ['LOOK_INDEX' => self::wrapped(Formula::number((float) $name), $this->model->lookCount($this->sprite_key))])],
            'next costume' === strtolower($name) => [$this->brick('NextLookBrick')],
            'previous costume' === strtolower($name) => [$this->brick('PreviousLookBrick')],
            default => [$this->note('switch to missing costume "'.$name.'"')],
          };
        }

        return [$this->brick('SetLookByIndexBrick', ['LOOK_INDEX' => self::byNameOrNumber($this->formulaOfInput($b, 'COSTUME', 'number'), $this->model->helper('costume names'), $this->model->lookCount($this->sprite_key))])];
      case 'looks_nextcostume': return [$this->brick('NextLookBrick')];
      case 'looks_switchbackdropto':
      case 'looks_nextbackdrop':
        return $this->switchBackdrop('looks_nextbackdrop' === $opcode ? 'next backdrop' : $this->menu($b, 'BACKDROP'), $b, false);
      case 'looks_switchbackdroptoandwait':
        return $this->switchBackdrop($this->menu($b, 'BACKDROP'), $b, true);
      case 'looks_seteffectto':
      case 'looks_changeeffectby':
        return $this->effect($b, 'looks_seteffectto' === $opcode);
      case 'looks_cleargraphiceffects': return [$this->brick('ClearGraphicEffectBrick')];
      case 'looks_changesizeby': return [$this->brick('ChangeSizeByNBrick', ['SIZE_CHANGE' => $this->number($b, 'CHANGE')])];
      case 'looks_setsizeto': return [$this->brick('SetSizeToBrick', ['SIZE' => $this->number($b, 'SIZE')])];
      case 'looks_show': return [$this->brick('ShowBrick')];
      case 'looks_hide': return [$this->brick('HideBrick')];
      case 'looks_gotofrontback':
        return 'front' === strtolower($this->field($b, 'FRONT_BACK'))
          ? [$this->brick('ComeToFrontBrick')]
          : [$this->brick('GoNStepsBackBrick', ['STEPS' => Formula::number(100000)])];
      case 'looks_goforwardbackwardlayers':
        $steps = $this->number($b, 'NUM');

        return [$this->brick('GoNStepsBackBrick', ['STEPS' => 'forward' === strtolower($this->field($b, 'FORWARD_BACKWARD')) ? Formula::unary('MINUS', $steps) : $steps])];

        // sound
      case 'sound_play':
      case 'sound_playuntildone':
        $sound = $this->soundOf($b);
        if (null === $sound) {
          return [$this->note('play sound with a computed name is not supported')];
        }

        return [$this->brick('sound_play' === $opcode ? 'PlaySoundBrick' : 'PlaySoundAndWaitBrick', [], [], [$sound])];
      case 'sound_stopallsounds': return [$this->brick('StopAllSoundsBrick')];
      case 'sound_setvolumeto': return [$this->brick('SetVolumeToBrick', ['VOLUME' => $this->number($b, 'VOLUME')])];
      case 'sound_changevolumeby': return [$this->brick('ChangeVolumeByNBrick', ['VOLUME_CHANGE' => $this->number($b, 'VOLUME')])];
        // events
      case 'event_broadcast':
      case 'event_broadcastandwait':
        $message = $this->broadcastName($b);
        if (null === $message) {
          return [$this->note('broadcast with a computed name is not supported')];
        }

        return [$this->brick('event_broadcast' === $opcode ? 'BroadcastBrick' : 'BroadcastWaitBrick', [], [], [$this->text('broadcastMessage', $message)])];

        // control
      case 'control_wait': return [$this->brick('WaitBrick', ['TIME_TO_WAIT_IN_SECONDS' => $this->number($b, 'DURATION')])];
      case 'control_repeat': return [$this->brick('RepeatBrick', ['TIMES_TO_REPEAT' => $this->number($b, 'TIMES')], [$this->list('loopBricks', $sub())])];
      case 'control_forever': return [$this->brick('ForeverBrick', [], [$this->list('loopBricks', $sub())])];
      case 'control_if': return [$this->brick('IfThenLogicBeginBrick', ['IF_CONDITION' => $this->condition($b, 'CONDITION')], [$this->list('ifBranchBricks', $sub())])];
      case 'control_if_else':
        return [$this->brick('IfLogicBeginBrick', ['IF_CONDITION' => $this->condition($b, 'CONDITION')], [
          $this->list('elseBranchBricks', $sub('SUBSTACK2')),
          $this->list('ifBranchBricks', $sub()),
        ])];
      case 'control_wait_until': return [$this->brick('WaitUntilBrick', ['IF_CONDITION' => $this->condition($b, 'CONDITION')])];
      case 'control_repeat_until': return [$this->brick('RepeatUntilBrick', ['REPEAT_UNTIL_CONDITION' => $this->condition($b, 'CONDITION')], [$this->list('loopBricks', $sub())])];
      case 'control_while': return [$this->brick('RepeatUntilBrick', ['REPEAT_UNTIL_CONDITION' => Formula::unary('LOGICAL_NOT', $this->condition($b, 'CONDITION'))], [$this->list('loopBricks', $sub())])];
      case 'control_stop':
        $option = strtolower($this->field($b, 'STOP_OPTION'));
        $selection = 'all' === $option ? '1' : ('this script' === $option ? '0' : '2');

        return [$this->brick('StopScriptBrick', [], [], [$this->text('spinnerSelection', $selection)])];
      case 'control_create_clone_of':
        $of = $this->menu($b, 'CLONE_OPTION');
        if (null === $of || '_myself_' === $of) {
          return [$this->brick('CloneBrick')];
        }

        $request = $this->model->cloneRequest($of);
        if (null === $request) {
          return [$this->note('create clone of an unknown sprite')];
        }

        // Count the request first: a broadcast that arrives while the handler still runs restarts it.
        return [
          $this->brick('ChangeVariableBrick', ['VARIABLE_CHANGE' => Formula::number(1)], [], [$this->model->variableElement(null, $request['counter'])]),
          $this->brick('BroadcastBrick', [], [], [$this->text('broadcastMessage', $request['message'])]),
        ];
      case 'control_delete_this_clone': return [$this->brick('DeleteThisCloneBrick')];
        // sensing
      case 'sensing_askandwait':
        return [$this->brick('AskBrick', ['ASK_QUESTION' => $this->textInput($b, 'QUESTION')], [], [$this->model->variableElement(null, $this->model->helper('answer'))])];
      case 'sensing_resettimer': return [$this->brick('ResetTimerBrick')];
        // data
      case 'data_setvariableto': return [$this->brick('SetVariableBrick', ['VARIABLE' => $this->textInput($b, 'VALUE')], [], [$this->variable($b)])];
      case 'data_changevariableby':
        // Catroid's ChangeVariableBrick skips variables that hold text, such as "0" after a set; PLUS reads numeric text.
        // Scratch counts other text as 0 where PLUS gives NaN: var = var + 0 holds only for numbers.
        // ponytail: the text "NaN" still gives NaN, Catroid's EQUAL treats NaN as equal to itself.
        $variable = Formula::variable($this->field($b, 'VARIABLE'));
        $current = Formula::fn('IF_THEN_ELSE', Formula::op('EQUAL', $variable, Formula::op('PLUS', $variable, Formula::number(0))), $variable, Formula::number(0));
        $sum = Formula::op('PLUS', $current, $this->number($b, 'VALUE'));

        return [$this->brick('SetVariableBrick', ['VARIABLE' => $sum], [], [$this->variable($b)])];
      case 'data_showvariable':
        return [$this->brick('ShowTextBrick', ['X_POSITION' => Formula::number(-220), 'Y_POSITION' => Formula::number(160)], [], [$this->variable($b)])];
      case 'data_hidevariable': return [$this->brick('HideTextBrick', [], [], [$this->variable($b)])];
      case 'data_addtolist': return [$this->brick('AddItemToUserListBrick', ['LIST_ADD_ITEM' => $this->textInput($b, 'ITEM')], [], [$this->userList($b)])];
      case 'data_deleteoflist':
        $index = $this->listIndex($b, 'INDEX');

        return null === $index
          ? [$this->brick('ClearUserListBrick', [], [], [$this->userList($b)], formula_list: true)]
          : [$this->brick('DeleteItemOfUserListBrick', ['LIST_DELETE_ITEM' => $index], [], [$this->userList($b)])];
      case 'data_deletealloflist': return [$this->brick('ClearUserListBrick', [], [], [$this->userList($b)], formula_list: true)];
      case 'data_insertatlist':
        return [$this->brick('InsertItemIntoUserListBrick', [
          'INSERT_ITEM_INTO_USERLIST_VALUE' => $this->textInput($b, 'ITEM'),
          'INSERT_ITEM_INTO_USERLIST_INDEX' => $this->listIndex($b, 'INDEX', true) ?? Formula::number(1),
        ], [], [$this->userList($b)])];
      case 'data_replaceitemoflist':
        return [$this->brick('ReplaceItemInUserListBrick', [
          'REPLACE_ITEM_IN_USERLIST_VALUE' => $this->textInput($b, 'ITEM'),
          'REPLACE_ITEM_IN_USERLIST_INDEX' => $this->listIndex($b, 'INDEX') ?? Formula::number(1),
        ], [], [$this->userList($b)])];

        // pen
      case 'pen_clear': return [$this->brick('ClearBackgroundBrick')];
      case 'pen_stamp': return [$this->brick('StampBrick')];
      case 'pen_penDown': return [$this->brick('PenDownBrick')];
      case 'pen_penUp': return [$this->brick('PenUpBrick')];
      case 'pen_setPenSizeTo': return [$this->brick('SetPenSizeBrick', ['PEN_SIZE' => $this->number($b, 'SIZE')])];
      case 'pen_setPenColorToColor':
        $color = $this->literalOf($b, 'COLOR');
        if (null === $color || 1 !== preg_match('/^#([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})/i', $color, $rgb)) {
          return [$this->note('pen color from a computed value is not supported')];
        }

        return [$this->brick('SetPenColorBrick', [
          'PEN_COLOR_RED' => Formula::number(hexdec($rgb[1])),
          'PEN_COLOR_GREEN' => Formula::number(hexdec($rgb[2])),
          'PEN_COLOR_BLUE' => Formula::number(hexdec($rgb[3])),
        ])];

        // custom blocks
      case 'procedures_call':
        return [$this->procedureCall($b)];

      default:
        $this->model->unsupported($opcode);

        return [$this->note($opcode.' is not supported in Pocket Code')];
    }
  }

  /**
   * @param list<\DOMElement>      $children nested brick lists, after formulaList
   * @param list<\DOMElement>      $fields   subclass fields, after the nested lists
   * @param array<string, Formula> $formulas
   */
  public function brick(string $type, array $formulas = [], array $children = [], array $fields = [], ?bool $formula_list = null): \DOMElement
  {
    $brick = $this->doc->createElement('brick');
    $brick->setAttribute('type', $type);
    $brick->appendChild($this->text('brickId', self::uuid()));
    $brick->appendChild($this->text('commentedOut', 'false'));
    if ($formula_list ?? [] !== $formulas) {
      $list = $this->doc->createElement('formulaList');
      foreach ($formulas as $category => $formula) {
        $list->appendChild($formula->toElement($this->doc, 'formula', ['category' => $category]));
      }

      $brick->appendChild($list);
    }

    foreach ([...$children, ...$fields] as $element) {
      $brick->appendChild($element);
    }

    return $brick;
  }

  public function note(string $text): \DOMElement
  {
    return $this->brick('NoteBrick', ['NOTE' => Formula::string('[Scratch] '.$text)]);
  }

  /** @return list<\DOMElement> */
  private function switchBackdrop(?string $name, array $b, bool $wait): array
  {
    $count = $this->model->backdropCount();
    $lower = strtolower((string) $name);
    if ('next backdrop' === $lower || 'previous backdrop' === $lower) {
      if ($this->model->isStage($this->sprite_key)) {
        return [$this->brick('next backdrop' === $lower ? 'NextLookBrick' : 'PreviousLookBrick')];
      }

      $message = $this->model->helper($lower);

      return [$this->brick($wait ? 'BroadcastWaitBrick' : 'BroadcastBrick', [], [], [$this->text('broadcastMessage', $message)])];
    }

    $type = $wait ? 'SetBackgroundByIndexAndWaitBrick' : 'SetBackgroundByIndexBrick';
    $category = $wait ? 'BACKGROUND_WAIT_INDEX' : 'BACKGROUND_INDEX';
    if ('random backdrop' === $lower) {
      return [$this->brick($type, [$category => Formula::fn('RAND', Formula::number(1), Formula::number($count))])];
    }

    $look = null === $name ? null : $this->model->backdropLook($name);
    if (null !== $look) {
      return [$this->brick($wait ? 'SetBackgroundAndWaitBrick' : 'SetBackgroundBrick', [], [], [$look])];
    }

    // A name that is no backdrop: Scratch reads it as a number, or stays on the current one and fires its scripts again.
    if (null !== $name) {
      return [$this->brick($type, [$category => is_numeric($name) ? self::wrapped(Formula::number((float) $name), $count) : Formula::sensor('OBJECT_BACKGROUND_NUMBER')])];
    }

    return [$this->brick($type, [$category => self::byNameOrNumber($this->formulaOfInput($b, 'BACKDROP', 'number'), $this->model->helper('backdrop names'), $count)])];
  }

  /** @return list<\DOMElement> */
  private function effect(array $b, bool $set): array
  {
    $effect = strtolower($this->field($b, 'EFFECT'));
    $value = $this->number($b, $set ? 'VALUE' : 'CHANGE');

    return match ($effect) {
      'color' => [$set ? $this->brick('SetColorBrick', ['COLOR' => $value]) : $this->brick('ChangeColorByNBrick', ['COLOR_CHANGE' => $value])],
      'ghost' => [$set ? $this->brick('SetTransparencyBrick', ['TRANSPARENCY' => $value]) : $this->brick('ChangeTransparencyByNBrick', ['TRANSPARENCY_CHANGE' => $value])],
      'brightness' => [$set
        ? $this->brick('SetBrightnessBrick', ['BRIGHTNESS' => Formula::op('PLUS', $value, Formula::number(100))])
        : $this->brick('ChangeBrightnessByNBrick', ['BRIGHTNESS_CHANGE' => $value])],
      default => [$this->note('graphic effect "'.$effect.'" is not supported')],
    };
  }

  private function procedureCall(array $b): \DOMElement
  {
    $proccode = (string) ($b['mutation']['proccode'] ?? '');
    $procedure = $this->procedures[$proccode] ?? null;
    if (null === $procedure) {
      return $this->note('call of the unknown custom block "'.$proccode.'"');
    }

    $list = $this->doc->createElement('formulaList');
    foreach ($procedure['ids'] as $i => $argument_id) {
      $formula = isset($b['inputs'][$argument_id]) ? $this->formulaOfInput($b, $argument_id, 'text') : Formula::string('');
      $list->appendChild($formula->toElement($this->doc, 'formula', ['input' => $procedure['names'][$i]]));
    }

    $brick = $this->brick('UserDefinedBrick');
    $brick->appendChild($list);
    $brick->appendChild($this->text('isCallingBrick', 'true'));
    $brick->appendChild($this->dataList($proccode));
    $brick->appendChild($this->text('userDefinedBrickID', $procedure['id']));

    return $brick;
  }

  private function dataList(string $proccode): \DOMElement
  {
    $list = $this->doc->createElement('userDefinedBrickDataList');
    $list->setAttribute(XStreamReferences::KEY, 'udb:'.$this->sprite_key.':'.$proccode);
    foreach ($this->procedures[$proccode]['labels'] as [$kind, $value]) {
      if ('label' === $kind) {
        $item = $this->doc->createElement('userDefinedBrickLabel');
        $item->appendChild($this->text('type', 'LABEL'));
        $item->appendChild($this->text('label', $value));
      } else {
        $item = $this->doc->createElement('userDefinedBrickInput');
        $item->appendChild($this->text('type', 'INPUT'));
        $item->appendChild($this->doc->createElement('input'))->appendChild($this->text('input', $value));
      }

      $list->appendChild($item);
    }

    return $list;
  }

  /** @return array<string, array{id: string, names: list<string>, ids: list<string>, labels: list<array{0: string, 1: string}>, warp: bool}> */
  private function collectProcedures(): array
  {
    $procedures = [];
    foreach ($this->blocks as $block) {
      if ('procedures_prototype' !== ($block['opcode'] ?? null)) {
        continue;
      }

      $mutation = $block['mutation'] ?? [];
      $proccode = (string) ($mutation['proccode'] ?? '');
      if ('' === $proccode || isset($procedures[$proccode])) {
        continue;
      }

      $ids = self::jsonList($mutation['argumentids'] ?? '[]');
      $names = [];
      foreach (self::jsonList($mutation['argumentnames'] ?? '[]') as $i => $name) {
        $unique = '' === trim($name) ? 'input' : $name;
        for ($n = 2; \in_array($unique, $names, true); ++$n) {
          $unique = $name.' '.$n;
        }

        $names[$i] = $unique;
      }

      $labels = [];
      $argument = 0;
      foreach (preg_split('/(%[snb])/', $proccode, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) ?: [] as $part) {
        if (1 === preg_match('/^%[snb]$/', $part)) {
          $labels[] = ['input', $names[$argument++] ?? 'input'];
        } elseif ('' !== trim($part)) {
          $labels[] = ['label', trim($part)];
        }
      }

      $procedures[$proccode] = [
        'id' => self::uuid(),
        'names' => array_values($names),
        'ids' => $ids,
        'labels' => $labels,
        'warp' => true === ($mutation['warp'] ?? false) || 'true' === ($mutation['warp'] ?? null),
      ];
    }

    return $procedures;
  }

  // ---- formulas ----

  private function number(array $b, string $name): Formula
  {
    return $this->formulaOfInput($b, $name, 'number');
  }

  private function textInput(array $b, string $name): Formula
  {
    return $this->formulaOfInput($b, $name, 'text');
  }

  private function condition(array $b, string $name): Formula
  {
    return $this->formulaOfInput($b, $name, 'bool');
  }

  /** @param 'number'|'text'|'bool' $kind */
  private function formulaOfInput(array $b, string $name, string $kind): Formula
  {
    $input = $b['inputs'][$name] ?? null;
    $value = \is_array($input) ? ($input[1] ?? null) : null;
    // Paid once per use: a literal, or a block's fields (a reporter pays again in guarded(), which errs on the safe side).
    $literal = \is_array($value) ? ($value[1] ?? null) : null;
    $block_text = \is_string($value) && isset($this->blocks[$value]) ? self::textBytes($this->blocks[$value]) : 0;
    $this->spend(\is_scalar($literal) ? \strlen((string) $literal) : $block_text);
    $default = match ($kind) {
      'number' => Formula::number(0),
      'text' => Formula::string(''),
      'bool' => Formula::false(),
    };

    if (\is_array($value)) {
      return $this->primitive($value, $kind) ?? $default;
    }

    if (!\is_string($value) || !isset($this->blocks[$value])) {
      return $default;
    }

    $block = $this->blocks[$value];
    if ('text' === ($block['opcode'] ?? null) && 'text' === $kind) {
      return Formula::string($this->field($block, 'TEXT')); // "001" stays text
    }

    if (($block['shadow'] ?? false) && !str_starts_with((string) ($block['opcode'] ?? ''), 'math_') && 'text' !== ($block['opcode'] ?? null) && [] !== ($block['fields'] ?? [])) {
      return Formula::literal((string) (reset($block['fields'])[0] ?? '')); // a menu, e.g. a costume name
    }

    return $this->guarded($value, fn (): Formula => $this->reporter($block));
  }

  /** @param 'number'|'text'|'bool' $kind */
  private function primitive(array $value, string $kind): ?Formula
  {
    return match ((int) ($value[0] ?? 0)) {
      4, 5, 6, 7, 8 => Formula::literal($value[1] ?? ''),
      10 => 'text' === $kind ? Formula::string((string) ($value[1] ?? '')) : Formula::literal($value[1] ?? ''),
      9, 11 => Formula::string((string) ($value[1] ?? '')),
      12 => Formula::variable((string) ($value[1] ?? '')),
      13 => Formula::userList((string) ($value[1] ?? '')),
      default => null,
    };
  }

  private function reporter(array $r): Formula
  {
    $opcode = (string) ($r['opcode'] ?? '');
    $n = fn (string $name): Formula => $this->number($r, $name);
    $t = fn (string $name): Formula => $this->textInput($r, $name);
    $c = fn (string $name): Formula => $this->condition($r, $name);

    switch ($opcode) {
      case 'math_number': case 'math_positive_number': case 'math_whole_number': case 'math_integer': case 'math_angle':
        return Formula::literal($this->field($r, 'NUM'));
      case 'text': return Formula::literal($this->field($r, 'TEXT'));
      case 'colour_picker': return Formula::string($this->field($r, 'COLOUR'));
        // operators
      case 'operator_add': return Formula::op('PLUS', $n('NUM1'), $n('NUM2'));
      case 'operator_subtract': return Formula::op('MINUS', $n('NUM1'), $n('NUM2'));
      case 'operator_multiply': return Formula::op('MULT', $n('NUM1'), $n('NUM2'));
      case 'operator_divide': return Formula::op('DIVIDE', $n('NUM1'), $n('NUM2'));
      case 'operator_lt': return Formula::op('SMALLER_THAN', $t('OPERAND1'), $t('OPERAND2'));
      case 'operator_equals': return Formula::op('EQUAL', $t('OPERAND1'), $t('OPERAND2'));
      case 'operator_gt': return Formula::op('GREATER_THAN', $t('OPERAND1'), $t('OPERAND2'));
      case 'operator_and': return Formula::op('LOGICAL_AND', $c('OPERAND1'), $c('OPERAND2'));
      case 'operator_or': return Formula::op('LOGICAL_OR', $c('OPERAND1'), $c('OPERAND2'));
      case 'operator_not': return Formula::unary('LOGICAL_NOT', $c('OPERAND'));
      case 'operator_random': return Formula::fn('RAND', $n('FROM'), $n('TO'));
      case 'operator_join': return Formula::fn('JOIN', $t('STRING1'), $t('STRING2'));
      case 'operator_letter_of': return Formula::fn('LETTER', $n('LETTER'), $t('STRING'));
      case 'operator_length': return Formula::fn('LENGTH', $t('STRING'));
      case 'operator_contains':
        // Case-insensitive literal substring search, as in Scratch.
        return Formula::op('NOT_EQUAL', Formula::fn('REGEX', Formula::fn('JOIN3', Formula::string('(?iu)\Q'), $t('STRING2'), Formula::string('\E')), $t('STRING1')), Formula::string(''));
      case 'operator_mod': return Formula::fn('MOD', $n('NUM1'), $n('NUM2'));
      case 'operator_round': return Formula::fn('ROUND', $n('NUM'));
      case 'operator_mathop':
        $x = $n('NUM');

        return match (strtolower($this->field($r, 'OPERATOR'))) {
          'abs' => Formula::fn('ABS', $x), 'floor' => Formula::fn('FLOOR', $x), 'ceiling' => Formula::fn('CEIL', $x),
          'sqrt' => Formula::fn('SQRT', $x), 'sin' => Formula::fn('SIN', $x), 'cos' => Formula::fn('COS', $x),
          'tan' => Formula::fn('TAN', $x), 'asin' => Formula::fn('ARCSIN', $x), 'acos' => Formula::fn('ARCCOS', $x),
          'atan' => Formula::fn('ARCTAN', $x), 'ln' => Formula::fn('LN', $x), 'log' => Formula::fn('LOG', $x),
          'e ^' => Formula::fn('EXP', $x), '10 ^' => Formula::fn('POWER', Formula::number(10), $x),
          default => $this->unsupportedReporter($opcode),
        };

        // motion / looks / sound
      case 'motion_xposition': return Formula::sensor('OBJECT_X');
      case 'motion_yposition': return Formula::sensor('OBJECT_Y');
      case 'motion_direction': return Formula::sensor('MOTION_DIRECTION');
      case 'looks_size': return Formula::sensor('OBJECT_SIZE');
      case 'looks_costumenumbername':
        return Formula::sensor('name' === strtolower($this->field($r, 'NUMBER_NAME')) ? 'OBJECT_LOOK_NAME' : 'OBJECT_LOOK_NUMBER');
      case 'looks_backdropnumbername':
        $name = 'name' === strtolower($this->field($r, 'NUMBER_NAME'));
        if ($this->model->isStage($this->sprite_key)) {
          return Formula::sensor($name ? 'OBJECT_BACKGROUND_NAME' : 'OBJECT_BACKGROUND_NUMBER');
        }

        return $this->model->shadowVariable(null, $name ? 'backdrop name' : 'backdrop #') ?? $this->unsupportedReporter($opcode);
      case 'sound_volume': return Formula::number(100);
        // sensing
      case 'sensing_touchingobject': return $this->touching($this->menu($r, 'TOUCHINGOBJECTMENU'));
      case 'sensing_touchingcolor': return Formula::fn('COLLIDES_WITH_COLOR', $t('COLOR'));
      case 'sensing_coloristouchingcolor': return Formula::fn('COLOR_TOUCHES_COLOR', $t('COLOR'), $t('COLOR2'));
      case 'sensing_distanceto':
        $to = $this->menu($r, 'DISTANCETOMENU');
        if ('_mouse_' === $to) {
          return Formula::sensor('OBJECT_DISTANCE_TO');
        }

        [$x, $y] = $this->targetPosition($to);

        return Formula::fn('SQRT', Formula::op('PLUS',
          Formula::fn('POWER', Formula::op('MINUS', $x, Formula::sensor('OBJECT_X')), Formula::number(2)),
          Formula::fn('POWER', Formula::op('MINUS', $y, Formula::sensor('OBJECT_Y')), Formula::number(2))));
      case 'sensing_answer': return Formula::variable($this->model->helper('answer'));
      case 'sensing_keypressed':
        $key = mb_strtolower($this->menu($r, 'KEY_OPTION') ?? '');
        $held = array_map(fn (string $k): Formula => Formula::variable($this->model->helper('key '.$k)), 'any' === $key ? $this->model->keys() : array_intersect([$key], $this->model->keys()));

        return array_reduce(array_slice($held, 1), static fn (Formula $any, Formula $k): Formula => Formula::op('LOGICAL_OR', $any, $k), $held[0] ?? Formula::false());
      case 'sensing_mousedown': return Formula::sensor('FINGER_TOUCHED');
      case 'sensing_mousex': return Formula::sensor('FINGER_X');
      case 'sensing_mousey': return Formula::sensor('FINGER_Y');
      case 'sensing_loudness': return Formula::sensor('LOUDNESS');
      case 'sensing_timer': return Formula::sensor('TIMER');
      case 'sensing_current':
        return match (strtolower($this->field($r, 'CURRENTMENU'))) {
          'year' => Formula::sensor('DATE_YEAR'), 'month' => Formula::sensor('DATE_MONTH'), 'date' => Formula::sensor('DATE_DAY'),
          'hour' => Formula::sensor('TIME_HOUR'), 'minute' => Formula::sensor('TIME_MINUTE'), 'second' => Formula::sensor('TIME_SECOND'),
          'dayofweek' => Formula::op('PLUS', Formula::fn('MOD', Formula::sensor('DATE_WEEKDAY'), Formula::number(7)), Formula::number(1)),
          default => $this->unsupportedReporter($opcode),
        };
      case 'sensing_dayssince2000': return Formula::number(round((time() - 946684800) / 86400, 2));
      case 'sensing_username': return Formula::string('');
      case 'sensing_of': return $this->sensingOf($r);
        // data
      case 'data_variable': return Formula::variable($this->field($r, 'VARIABLE'));
      case 'data_listcontents': return Formula::userList($this->field($r, 'LIST'));
      case 'data_itemoflist': return Formula::fn('LIST_ITEM', $this->listIndex($r, 'INDEX') ?? Formula::number(1), Formula::userList($this->field($r, 'LIST')));
      case 'data_itemnumoflist': return Formula::fn('INDEX_OF_ITEM', $t('ITEM'), Formula::userList($this->field($r, 'LIST')));
      case 'data_lengthoflist': return Formula::fn('NUMBER_OF_ITEMS', Formula::userList($this->field($r, 'LIST')));
      case 'data_listcontainsitem': return Formula::fn('CONTAINS', Formula::userList($this->field($r, 'LIST')), $t('ITEM'));
        // custom block arguments
      case 'argument_reporter_string_number':
      case 'argument_reporter_boolean':
        $argument = $this->field($r, 'VALUE');

        return null !== $this->arguments && \in_array($argument, $this->arguments, true) ? Formula::input($argument) : Formula::string('');

      default:
        return $this->unsupportedReporter($opcode);
    }
  }

  /**
   * Scratch switches to the costume with that name, else treats the value as a number. The converter gives
   * such sprites a list of their costume names to look the name up in.
   */
  private static function byNameOrNumber(Formula $value, string $names, int $count): Formula
  {
    $index = Formula::fn('INDEX_OF_ITEM', $value, Formula::userList($names));

    return Formula::fn('IF_THEN_ELSE', Formula::op('GREATER_THAN', $index, Formula::number(0)), $index, self::wrapped($value, $count));
  }

  /**
   * Numbers wrap around like in Scratch: costume 4 of 3 is costume 1. Not with MOD: Catroid's MOD adds the divisor
   * in a loop until a negative dividend turns positive, which never ends for -1e20. ((r-1) - n*floor((r-1)/n)) + 1.
   */
  private static function wrapped(Formula $value, int $count): Formula
  {
    $zero_based = Formula::op('MINUS', Formula::fn('ROUND', $value), Formula::number(1));
    $n = Formula::number(max(1, $count));

    return Formula::op('PLUS', Formula::op('MINUS', $zero_based, Formula::op('MULT', $n, Formula::fn('FLOOR', Formula::op('DIVIDE', $zero_based, $n)))), Formula::number(1));
  }

  private function unsupportedReporter(string $opcode): Formula
  {
    $this->model->unsupported($opcode);

    return Formula::number(0);
  }

  private function sensingOf(array $r): Formula
  {
    $object = $this->menu($r, 'OBJECT');
    $property = $this->field($r, 'PROPERTY');
    if (null === $object) {
      return $this->unsupportedReporter('sensing_of');
    }

    if ('_stage_' === $object) {
      return match (strtolower($property)) {
        'backdrop #', 'backdrop name' => $this->model->isStage($this->sprite_key)
          ? Formula::sensor('backdrop name' === strtolower($property) ? 'OBJECT_BACKGROUND_NAME' : 'OBJECT_BACKGROUND_NUMBER')
          : ($this->model->shadowVariable(null, strtolower($property)) ?? $this->unsupportedReporter('sensing_of')),
        'volume' => Formula::number(100),
        default => Formula::variable($property), // a global variable
      };
    }

    return $this->model->shadowVariable($object, $property) ?? $this->unsupportedReporter('sensing_of');
  }

  private function touching(?string $what): Formula
  {
    return match ($what) {
      null => Formula::false(),
      '_mouse_' => Formula::sensor('COLLIDES_WITH_FINGER'),
      '_edge_' => Formula::sensor('COLLIDES_WITH_EDGE'),
      default => Formula::collision($what),
    };
  }

  /** @return array{0: Formula, 1: Formula} x and y of a motion target menu value */
  private function targetPosition(?string $target): array
  {
    return match ($target) {
      '_mouse_' => [Formula::sensor('FINGER_X'), Formula::sensor('FINGER_Y')],
      '_random_', null => [Formula::fn('RAND', Formula::number(-240), Formula::number(240)), Formula::fn('RAND', Formula::number(-180), Formula::number(180))],
      default => [$this->model->shadowVariable($target, 'x position') ?? Formula::number(0), $this->model->shadowVariable($target, 'y position') ?? Formula::number(0)],
    };
  }

  /** Scratch list indexes accept "last", "random" and "all"; null means "all". */
  private function listIndex(array $b, string $name, bool $insert = false): ?Formula
  {
    $literal = $this->literalOf($b, $name);
    $list = Formula::userList($this->field($b, 'LIST'));

    return match (null === $literal ? null : strtolower(trim($literal))) {
      'all' => null,
      'last' => $insert ? Formula::op('PLUS', Formula::fn('NUMBER_OF_ITEMS', $list), Formula::number(1)) : Formula::fn('NUMBER_OF_ITEMS', $list),
      'random', 'any' => Formula::fn('RAND', Formula::number(1), Formula::fn('NUMBER_OF_ITEMS', $list)),
      default => $this->number($b, $name),
    };
  }

  // ---- Scratch input helpers ----

  /** Value of a field such as VARIABLE: ["name", "id"]. */
  private function field(array $b, string $name): string
  {
    return (string) ($b['fields'][$name][0] ?? '');
  }

  private function inputBlockId(array $b, string $name): ?string
  {
    $value = $b['inputs'][$name][1] ?? null;

    return \is_string($value) ? $value : null;
  }

  /** The literal behind an input (a primitive or a shadow menu/text block), or null if it is computed. */
  private function literalOf(array $b, string $name): ?string
  {
    $input = $b['inputs'][$name] ?? null;
    if (!\is_array($input)) {
      return null;
    }

    $value = $input[1] ?? null;
    if (\is_array($value)) {
      return isset($value[1]) ? (string) $value[1] : null;
    }

    if (!\is_string($value) || !isset($this->blocks[$value]) || !($this->blocks[$value]['shadow'] ?? false)) {
      return null;
    }

    $fields = $this->blocks[$value]['fields'] ?? [];

    return [] === $fields ? null : (string) (reset($fields)[0] ?? '');
  }

  /** Selected value of a menu input (its shadow block holds the same-named field), or null if a reporter was dropped in. */
  private function menu(array $b, string $name): ?string
  {
    if (isset($b['fields'][$name])) {
      return $this->field($b, $name);
    }

    $block = $this->blocks[$this->inputBlockId($b, $name) ?? ''] ?? null;

    return null !== $block && ($block['shadow'] ?? false) ? $this->field($block, $name) : null;
  }

  private function broadcastName(array $b): ?string
  {
    $value = $b['inputs']['BROADCAST_INPUT'][1] ?? null;
    if (\is_array($value) && 11 === (int) ($value[0] ?? 0)) {
      return (string) $value[1];
    }

    $block = \is_string($value) ? ($this->blocks[$value] ?? null) : null;

    return null !== $block && 'event_broadcast_menu' === ($block['opcode'] ?? null) ? $this->field($block, 'BROADCAST_OPTION') : null;
  }

  private function soundOf(array $b): ?\DOMElement
  {
    $name = $this->menu($b, 'SOUND_MENU');

    return null === $name ? null : $this->model->sound($this->sprite_key, $name);
  }

  private function variable(array $b): \DOMElement
  {
    $field = $b['fields']['VARIABLE'] ?? ['', null];

    return $this->model->variableElement($this->sprite_key, (string) $field[0], isset($field[1]) ? (string) $field[1] : null);
  }

  private function userList(array $b): \DOMElement
  {
    $field = $b['fields']['LIST'] ?? ['', null];

    return $this->model->listElement($this->sprite_key, (string) $field[0], isset($field[1]) ? (string) $field[1] : null);
  }

  // ---- XML helpers ----

  public function text(string $name, string $value): \DOMElement
  {
    $element = $this->doc->createElement($name);
    $element->appendChild($this->doc->createTextNode($value));

    return $element;
  }

  /** @param list<\DOMElement> $children */
  public function list(string $name, array $children): \DOMElement
  {
    $element = $this->doc->createElement($name);
    foreach ($children as $child) {
      $element->appendChild($child);
    }

    return $element;
  }

  /** @param array<string, mixed> $block */
  private static function textBytes(array $block): int
  {
    $bytes = \strlen(\is_scalar($block['opcode'] ?? null) ? (string) $block['opcode'] : '');
    foreach ((array) ($block['fields'] ?? []) as $field) {
      $bytes += \is_array($field) && \is_scalar($field[0] ?? null) ? \strlen((string) $field[0]) : 0;
    }

    $proccode = $block['mutation']['proccode'] ?? null;

    return $bytes + (\is_scalar($proccode) ? \strlen((string) $proccode) : 0);
  }

  /** @return list<string> */
  private static function jsonList(mixed $json): array
  {
    $data = \is_string($json) ? json_decode($json, true) : $json;

    return \is_array($data) ? array_map(strval(...), array_values($data)) : [];
  }

  public static function uuid(): string
  {
    $bytes = random_bytes(16);
    $bytes[6] = \chr(\ord($bytes[6]) & 0x0F | 0x40);
    $bytes[8] = \chr(\ord($bytes[8]) & 0x3F | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
  }
}
