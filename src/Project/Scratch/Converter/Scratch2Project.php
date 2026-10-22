<?php

declare(strict_types=1);

namespace App\Project\Scratch\Converter;

/**
 * Turns a Scratch 2 project.json (projects not saved since 2019) into the Scratch 3 shape the converter reads.
 * A port of scratch-vm's src/serialization/sb2.js; the opcode table is generated from its sb2_specmap.js.
 * The format is frozen, so this needs no upkeep.
 */
final class Scratch2Project
{
  /**
   * Scratch 2 opcode => [Scratch 3 opcode, ...positional arguments]; an argument is ['i', input, shadow opcode],
   * ['f', field] or null (skipped). Generated from scratch-vm's sb2_specmap.js, extension blocks left out.
   */
  private const array SPEC = [
    'forward:' => ['motion_movesteps', ['i', 'STEPS', 'math_number']],
    'turnRight:' => ['motion_turnright', ['i', 'DEGREES', 'math_number']],
    'turnLeft:' => ['motion_turnleft', ['i', 'DEGREES', 'math_number']],
    'heading:' => ['motion_pointindirection', ['i', 'DIRECTION', 'math_angle']],
    'pointTowards:' => ['motion_pointtowards', ['i', 'TOWARDS', 'motion_pointtowards_menu']],
    'gotoX:y:' => ['motion_gotoxy', ['i', 'X', 'math_number'], ['i', 'Y', 'math_number']],
    'gotoSpriteOrMouse:' => ['motion_goto', ['i', 'TO', 'motion_goto_menu']],
    'glideSecs:toX:y:elapsed:from:' => ['motion_glidesecstoxy', ['i', 'SECS', 'math_number'], ['i', 'X', 'math_number'], ['i', 'Y', 'math_number']],
    'changeXposBy:' => ['motion_changexby', ['i', 'DX', 'math_number']],
    'xpos:' => ['motion_setx', ['i', 'X', 'math_number']],
    'changeYposBy:' => ['motion_changeyby', ['i', 'DY', 'math_number']],
    'ypos:' => ['motion_sety', ['i', 'Y', 'math_number']],
    'bounceOffEdge' => ['motion_ifonedgebounce'],
    'setRotationStyle' => ['motion_setrotationstyle', ['f', 'STYLE']],
    'xpos' => ['motion_xposition'],
    'ypos' => ['motion_yposition'],
    'heading' => ['motion_direction'],
    'scrollRight' => ['motion_scroll_right', ['i', 'DISTANCE', 'math_number']],
    'scrollUp' => ['motion_scroll_up', ['i', 'DISTANCE', 'math_number']],
    'scrollAlign' => ['motion_align_scene', ['f', 'ALIGNMENT']],
    'xScroll' => ['motion_xscroll'],
    'yScroll' => ['motion_yscroll'],
    'say:duration:elapsed:from:' => ['looks_sayforsecs', ['i', 'MESSAGE', 'text'], ['i', 'SECS', 'math_number']],
    'say:' => ['looks_say', ['i', 'MESSAGE', 'text']],
    'think:duration:elapsed:from:' => ['looks_thinkforsecs', ['i', 'MESSAGE', 'text'], ['i', 'SECS', 'math_number']],
    'think:' => ['looks_think', ['i', 'MESSAGE', 'text']],
    'show' => ['looks_show'],
    'hide' => ['looks_hide'],
    'hideAll' => ['looks_hideallsprites'],
    'lookLike:' => ['looks_switchcostumeto', ['i', 'COSTUME', 'looks_costume']],
    'nextCostume' => ['looks_nextcostume'],
    'startScene' => ['looks_switchbackdropto', ['i', 'BACKDROP', 'looks_backdrops']],
    'changeGraphicEffect:by:' => ['looks_changeeffectby', ['f', 'EFFECT'], ['i', 'CHANGE', 'math_number']],
    'setGraphicEffect:to:' => ['looks_seteffectto', ['f', 'EFFECT'], ['i', 'VALUE', 'math_number']],
    'filterReset' => ['looks_cleargraphiceffects'],
    'changeSizeBy:' => ['looks_changesizeby', ['i', 'CHANGE', 'math_number']],
    'setSizeTo:' => ['looks_setsizeto', ['i', 'SIZE', 'math_number']],
    'changeStretchBy:' => ['looks_changestretchby', ['i', 'CHANGE', 'math_number']],
    'setStretchTo:' => ['looks_setstretchto', ['i', 'STRETCH', 'math_number']],
    'comeToFront' => ['looks_gotofrontback'],
    'goBackByLayers:' => ['looks_goforwardbackwardlayers', ['i', 'NUM', 'math_integer']],
    'costumeIndex' => ['looks_costumenumbername'],
    'costumeName' => ['looks_costumenumbername'],
    'sceneName' => ['looks_backdropnumbername'],
    'scale' => ['looks_size'],
    'startSceneAndWait' => ['looks_switchbackdroptoandwait', ['i', 'BACKDROP', 'looks_backdrops']],
    'nextScene' => ['looks_nextbackdrop'],
    'backgroundIndex' => ['looks_backdropnumbername'],
    'playSound:' => ['sound_play', ['i', 'SOUND_MENU', 'sound_sounds_menu']],
    'doPlaySoundAndWait' => ['sound_playuntildone', ['i', 'SOUND_MENU', 'sound_sounds_menu']],
    'stopAllSounds' => ['sound_stopallsounds'],
    'playDrum' => ['music_playDrumForBeats', ['i', 'DRUM', 'music_menu_DRUM'], ['i', 'BEATS', 'math_number']],
    'drum:duration:elapsed:from:' => ['music_midiPlayDrumForBeats', ['i', 'DRUM', 'math_number'], ['i', 'BEATS', 'math_number']],
    'rest:elapsed:from:' => ['music_restForBeats', ['i', 'BEATS', 'math_number']],
    'noteOn:duration:elapsed:from:' => ['music_playNoteForBeats', ['i', 'NOTE', 'note'], ['i', 'BEATS', 'math_number']],
    'instrument:' => ['music_setInstrument', ['i', 'INSTRUMENT', 'music_menu_INSTRUMENT']],
    'midiInstrument:' => ['music_midiSetInstrument', ['i', 'INSTRUMENT', 'math_number']],
    'changeVolumeBy:' => ['sound_changevolumeby', ['i', 'VOLUME', 'math_number']],
    'setVolumeTo:' => ['sound_setvolumeto', ['i', 'VOLUME', 'math_number']],
    'volume' => ['sound_volume'],
    'changeTempoBy:' => ['music_changeTempo', ['i', 'TEMPO', 'math_number']],
    'setTempoTo:' => ['music_setTempo', ['i', 'TEMPO', 'math_number']],
    'tempo' => ['music_getTempo'],
    'clearPenTrails' => ['pen_clear'],
    'stampCostume' => ['pen_stamp'],
    'putPenDown' => ['pen_penDown'],
    'putPenUp' => ['pen_penUp'],
    'penColor:' => ['pen_setPenColorToColor', ['i', 'COLOR', 'colour_picker']],
    'changePenHueBy:' => ['pen_changePenHueBy', ['i', 'HUE', 'math_number']],
    'setPenHueTo:' => ['pen_setPenHueToNumber', ['i', 'HUE', 'math_number']],
    'changePenShadeBy:' => ['pen_changePenShadeBy', ['i', 'SHADE', 'math_number']],
    'setPenShadeTo:' => ['pen_setPenShadeToNumber', ['i', 'SHADE', 'math_number']],
    'changePenSizeBy:' => ['pen_changePenSizeBy', ['i', 'SIZE', 'math_number']],
    'penSize:' => ['pen_setPenSizeTo', ['i', 'SIZE', 'math_number']],
    'senseVideoMotion' => ['videoSensing_videoOn', ['i', 'ATTRIBUTE', 'videoSensing_menu_ATTRIBUTE'], ['i', 'SUBJECT', 'videoSensing_menu_SUBJECT']],
    'whenGreenFlag' => ['event_whenflagclicked'],
    'whenKeyPressed' => ['event_whenkeypressed', ['f', 'KEY_OPTION']],
    'whenClicked' => ['event_whenthisspriteclicked'],
    'whenSceneStarts' => ['event_whenbackdropswitchesto', ['f', 'BACKDROP']],
    'whenIReceive' => ['event_whenbroadcastreceived', ['f', 'BROADCAST_OPTION']],
    'broadcast:' => ['event_broadcast', ['i', 'BROADCAST_INPUT', 'event_broadcast_menu']],
    'doBroadcastAndWait' => ['event_broadcastandwait', ['i', 'BROADCAST_INPUT', 'event_broadcast_menu']],
    'wait:elapsed:from:' => ['control_wait', ['i', 'DURATION', 'math_positive_number']],
    'doRepeat' => ['control_repeat', ['i', 'TIMES', 'math_whole_number'], ['i', 'SUBSTACK', 'substack']],
    'doForever' => ['control_forever', ['i', 'SUBSTACK', 'substack']],
    'doIf' => ['control_if', ['i', 'CONDITION', 'boolean'], ['i', 'SUBSTACK', 'substack']],
    'doIfElse' => ['control_if_else', ['i', 'CONDITION', 'boolean'], ['i', 'SUBSTACK', 'substack'], ['i', 'SUBSTACK2', 'substack']],
    'doWaitUntil' => ['control_wait_until', ['i', 'CONDITION', 'boolean']],
    'doUntil' => ['control_repeat_until', ['i', 'CONDITION', 'boolean'], ['i', 'SUBSTACK', 'substack']],
    'doWhile' => ['control_while', ['i', 'CONDITION', 'boolean'], ['i', 'SUBSTACK', 'substack']],
    'doForLoop' => ['control_for_each', ['f', 'VARIABLE'], ['i', 'VALUE', 'text'], ['i', 'SUBSTACK', 'substack']],
    'stopScripts' => ['control_stop', ['f', 'STOP_OPTION']],
    'whenCloned' => ['control_start_as_clone'],
    'createCloneOf' => ['control_create_clone_of', ['i', 'CLONE_OPTION', 'control_create_clone_of_menu']],
    'deleteClone' => ['control_delete_this_clone'],
    'COUNT' => ['control_get_counter'],
    'INCR_COUNT' => ['control_incr_counter'],
    'CLR_COUNT' => ['control_clear_counter'],
    'warpSpeed' => ['control_all_at_once', ['i', 'SUBSTACK', 'substack']],
    'touching:' => ['sensing_touchingobject', ['i', 'TOUCHINGOBJECTMENU', 'sensing_touchingobjectmenu']],
    'touchingColor:' => ['sensing_touchingcolor', ['i', 'COLOR', 'colour_picker']],
    'color:sees:' => ['sensing_coloristouchingcolor', ['i', 'COLOR', 'colour_picker'], ['i', 'COLOR2', 'colour_picker']],
    'distanceTo:' => ['sensing_distanceto', ['i', 'DISTANCETOMENU', 'sensing_distancetomenu']],
    'doAsk' => ['sensing_askandwait', ['i', 'QUESTION', 'text']],
    'answer' => ['sensing_answer'],
    'keyPressed:' => ['sensing_keypressed', ['i', 'KEY_OPTION', 'sensing_keyoptions']],
    'mousePressed' => ['sensing_mousedown'],
    'mouseX' => ['sensing_mousex'],
    'mouseY' => ['sensing_mousey'],
    'soundLevel' => ['sensing_loudness'],
    'isLoud' => ['sensing_loud'],
    'setVideoState' => ['videoSensing_videoToggle', ['i', 'VIDEO_STATE', 'videoSensing_menu_VIDEO_STATE']],
    'setVideoTransparency' => ['videoSensing_setVideoTransparency', ['i', 'TRANSPARENCY', 'math_number']],
    'timer' => ['sensing_timer'],
    'timerReset' => ['sensing_resettimer'],
    'getAttribute:of:' => ['sensing_of', ['f', 'PROPERTY'], ['i', 'OBJECT', 'sensing_of_object_menu']],
    'timeAndDate' => ['sensing_current', ['f', 'CURRENTMENU']],
    'timestamp' => ['sensing_dayssince2000'],
    'getUserName' => ['sensing_username'],
    'getUserId' => ['sensing_userid'],
    '+' => ['operator_add', ['i', 'NUM1', 'math_number'], ['i', 'NUM2', 'math_number']],
    '-' => ['operator_subtract', ['i', 'NUM1', 'math_number'], ['i', 'NUM2', 'math_number']],
    '*' => ['operator_multiply', ['i', 'NUM1', 'math_number'], ['i', 'NUM2', 'math_number']],
    '/' => ['operator_divide', ['i', 'NUM1', 'math_number'], ['i', 'NUM2', 'math_number']],
    'randomFrom:to:' => ['operator_random', ['i', 'FROM', 'math_number'], ['i', 'TO', 'math_number']],
    '<' => ['operator_lt', ['i', 'OPERAND1', 'text'], ['i', 'OPERAND2', 'text']],
    '=' => ['operator_equals', ['i', 'OPERAND1', 'text'], ['i', 'OPERAND2', 'text']],
    '>' => ['operator_gt', ['i', 'OPERAND1', 'text'], ['i', 'OPERAND2', 'text']],
    '&' => ['operator_and', ['i', 'OPERAND1', 'boolean'], ['i', 'OPERAND2', 'boolean']],
    '|' => ['operator_or', ['i', 'OPERAND1', 'boolean'], ['i', 'OPERAND2', 'boolean']],
    'not' => ['operator_not', ['i', 'OPERAND', 'boolean']],
    'concatenate:with:' => ['operator_join', ['i', 'STRING1', 'text'], ['i', 'STRING2', 'text']],
    'letter:of:' => ['operator_letter_of', ['i', 'LETTER', 'math_whole_number'], ['i', 'STRING', 'text']],
    'stringLength:' => ['operator_length', ['i', 'STRING', 'text']],
    '%' => ['operator_mod', ['i', 'NUM1', 'math_number'], ['i', 'NUM2', 'math_number']],
    'rounded' => ['operator_round', ['i', 'NUM', 'math_number']],
    'computeFunction:of:' => ['operator_mathop', ['f', 'OPERATOR'], ['i', 'NUM', 'math_number']],
    'readVariable' => ['data_variable', ['f', 'VARIABLE']],
    'getVar:' => ['data_variable', ['f', 'VARIABLE']],
    'setVar:to:' => ['data_setvariableto', ['f', 'VARIABLE'], ['i', 'VALUE', 'text']],
    'changeVar:by:' => ['data_changevariableby', ['f', 'VARIABLE'], ['i', 'VALUE', 'math_number']],
    'showVariable:' => ['data_showvariable', ['f', 'VARIABLE']],
    'hideVariable:' => ['data_hidevariable', ['f', 'VARIABLE']],
    'contentsOfList:' => ['data_listcontents', ['f', 'LIST']],
    'append:toList:' => ['data_addtolist', ['i', 'ITEM', 'text'], ['f', 'LIST']],
    'deleteLine:ofList:' => ['data_deleteoflist', ['i', 'INDEX', 'math_integer'], ['f', 'LIST']],
    'insert:at:ofList:' => ['data_insertatlist', ['i', 'ITEM', 'text'], ['i', 'INDEX', 'math_integer'], ['f', 'LIST']],
    'setLine:ofList:to:' => ['data_replaceitemoflist', ['i', 'INDEX', 'math_integer'], ['f', 'LIST'], ['i', 'ITEM', 'text']],
    'getLine:ofList:' => ['data_itemoflist', ['i', 'INDEX', 'math_integer'], ['f', 'LIST']],
    'lineCountOfList:' => ['data_lengthoflist', ['f', 'LIST']],
    'list:contains:' => ['data_listcontainsitem', ['f', 'LIST'], ['i', 'ITEM', 'text']],
    'showList:' => ['data_showlist', ['f', 'LIST']],
    'hideList:' => ['data_hidelist', ['f', 'LIST']],
    'procDef' => ['procedures_definition'],
    'getParam' => ['argument_reporter_string_number', ['f', 'VALUE']],
    'call' => ['procedures_call'],
  ];

  private const array NUMBER_SHADOWS = ['math_number' => 4, 'math_positive_number' => 5, 'math_whole_number' => 6, 'math_integer' => 7, 'math_angle' => 8];

  /** @var array<string, array<string, mixed>> */
  private array $blocks = [];

  /** @var array<string, string> lower-cased message => id */
  private array $messages = [];

  /** @var array<string, string> broadcast id => name */
  private array $broadcasts = [];

  private int $next_id = 0;

  /** @var array<string, string> this target's variable and list names => ids, with the stage's as fallback */
  private array $names = [];

  /** @var array<string, string> */
  private array $stage_names = [];

  private function __construct()
  {
  }

  /**
   * @param array<string, mixed> $sb2
   *
   * @return array<string, mixed> a Scratch 3 project.json
   */
  public static function toScratch3(array $sb2): array
  {
    $self = new self();
    $stage = $self->target($sb2, true, 0);
    $targets = [$stage];
    $layer = 0;
    foreach ((array) ($sb2['children'] ?? []) as $child) {
      if (\is_array($child) && isset($child['objName'])) { // others are variable and list watchers
        $targets[] = $self->target($child, false, ++$layer);
      }
    }

    $targets[0]['broadcasts'] = $self->broadcasts;

    return ['targets' => $targets, 'monitors' => [], 'extensions' => [], 'meta' => ['semver' => '3.0.0']];
  }

  /**
   * @param array<string, mixed> $object
   *
   * @return array<string, mixed>
   */
  private function target(array $object, bool $stage, int $layer): array
  {
    $prefix = $stage ? 'stage' : 'sprite'.$layer;
    $variables = [];
    $lists = [];
    $this->names = $stage ? [] : $this->stage_names;
    foreach ((array) ($object['variables'] ?? []) as $variable) {
      $id = $prefix.'-v-'.$variable['name'];
      $variables[$id] = [(string) $variable['name'], $variable['value'] ?? 0];
      $this->names['v'.$variable['name']] = $id;
    }

    foreach ((array) ($object['lists'] ?? []) as $list) {
      $id = $prefix.'-l-'.$list['listName'];
      $lists[$id] = [(string) $list['listName'], array_values((array) ($list['contents'] ?? []))];
      $this->names['l'.$list['listName']] = $id;
    }

    if ($stage) {
      $this->stage_names = $this->names;
    }

    $this->blocks = [];
    foreach ((array) ($object['scripts'] ?? []) as $script) {
      $first = \is_array($script) && \is_array($script[2] ?? null) ? $this->stack($script[2], null) : null;
      if (null !== $first) {
        $this->blocks[$first]['topLevel'] = true;
        $this->blocks[$first]['x'] = (int) ($script[0] ?? 0);
        $this->blocks[$first]['y'] = (int) ($script[1] ?? 0);
      }
    }

    $costumes = [];
    foreach ((array) ($object['costumes'] ?? []) as $costume) {
      $resolution = (int) ($costume['bitmapResolution'] ?? 1) ?: 1;
      [$md5, $extension] = self::asset((string) ($costume['baseLayerMD5'] ?? ''), 'png');
      $costumes[] = [
        'name' => (string) ($costume['costumeName'] ?? ''), 'bitmapResolution' => $resolution, 'assetId' => $md5,
        'md5ext' => $md5.'.'.$extension, 'dataFormat' => $extension,
        'rotationCenterX' => $stage ? 240 * $resolution : ($costume['rotationCenterX'] ?? 0),
        'rotationCenterY' => $stage ? 180 * $resolution : ($costume['rotationCenterY'] ?? 0),
        // Text typed in the Scratch 2 paint editor is a separate image drawn over the costume.
        ...isset($costume['textLayerMD5']) ? ['textLayerMd5ext' => self::asset((string) $costume['textLayerMD5'], 'png')[0].'.png'] : [],
      ];
    }

    $sounds = [];
    foreach ((array) ($object['sounds'] ?? []) as $sound) {
      [$md5, $extension] = self::asset((string) ($sound['md5'] ?? ''), 'wav');
      $sounds[] = ['name' => (string) ($sound['soundName'] ?? ''), 'assetId' => $md5, 'md5ext' => $md5.'.'.$extension, 'dataFormat' => $extension];
    }

    $direction = fmod((float) ($object['direction'] ?? 90) + 179, 360);

    return [
      'isStage' => $stage, 'name' => $stage ? 'Stage' : (string) $object['objName'],
      'variables' => $variables, 'lists' => $lists, 'broadcasts' => [], 'blocks' => $this->blocks, 'comments' => [],
      'currentCostume' => max(0, min(\count($costumes) - 1, (int) floor((float) ($object['currentCostumeIndex'] ?? 0)))),
      'costumes' => $costumes, 'sounds' => $sounds, 'layerOrder' => $layer, 'volume' => 100,
      'tempo' => $object['tempoBPM'] ?? 60, 'visible' => (bool) ($object['visible'] ?? true),
      'x' => $object['scratchX'] ?? 0, 'y' => $object['scratchY'] ?? 0, 'size' => 100 * (float) ($object['scale'] ?? 1),
      'direction' => ($direction < 0 ? $direction + 360 : $direction) - 179, 'draggable' => (bool) ($object['isDraggable'] ?? false),
      'rotationStyle' => ['none' => "don't rotate", 'leftRight' => 'left-right'][$object['rotationStyle'] ?? ''] ?? 'all around',
    ];
  }

  /** @return array{0: string, 1: string} md5 and file extension */
  private static function asset(string $md5ext, string $default): array
  {
    $parts = explode('.', $md5ext, 2);

    return [$parts[0], strtolower($parts[1] ?? '') ?: $default];
  }

  /**
   * A list of Scratch 2 blocks becomes a chain linked by next/parent.
   *
   * @param array<mixed> $list
   */
  private function stack(array $list, ?string $parent): ?string
  {
    $first = null;
    $previous = null;
    foreach ($list as $sb2) {
      $id = $this->block($sb2, $previous ?? $parent, false);
      if (null === $id) {
        continue;
      }

      if (null === $previous) {
        $first = $id;
      } else {
        $this->blocks[$previous]['next'] = $id;
      }

      $previous = $id;
    }

    return $first;
  }

  private function block(mixed $sb2, ?string $parent, bool $in_boolean): ?string
  {
    if (!\is_array($sb2) || !\is_string($sb2[0] ?? null)) {
      return null;
    }

    $old = $sb2[0];
    $spec = self::SPEC[$old] ?? match ($old) {
      'whenSensorGreaterThan' => 'video motion' === ($sb2[1] ?? null)
        ? ['videoSensing_whenMotionGreaterThan'] // unsupported, as in Scratch 3
        : ['event_whengreaterthan', ['f', 'WHENGREATERTHANMENU'], ['i', 'VALUE', 'math_number']],
      default => ['scratch2_'.$old], // unknown: the converter reports it as unsupported
    };
    if ('call' === $old) { // ['call', proc code, ...arguments]
      $spec = ['procedures_call', null];
      foreach (self::argumentTypes((string) ($sb2[1] ?? '')) as $i => $type) {
        $spec[] = ['i', 'input'.$i, ['n' => 'math_number', 's' => 'text', 'b' => 'boolean'][$type]];
      }
    }

    $id = 'sb2-'.++$this->next_id;
    $this->blocks[$id] = ['opcode' => array_shift($spec), 'next' => null, 'parent' => $parent, 'inputs' => [], 'fields' => [], 'shadow' => false, 'topLevel' => false];
    foreach ($spec as $i => $argument) {
      $value = $sb2[$i + 1] ?? null;
      if (!\is_array($argument)) {
        continue; // a skipped position, e.g. the proc code of a call
      }

      if (3 === \count($argument)) {
        $this->blocks[$id]['inputs'][$argument[1]] = $this->input($id, $argument[2], $value);
      } else {
        $this->blocks[$id]['fields'][$argument[1]] = $this->field($argument[1], $value);
      }
    }

    $this->blocks[$id] = self::special($old, $sb2, $this->blocks[$id], $in_boolean);
    if ('procDef' === $old) {
      $prototype = 'sb2-'.++$this->next_id;
      $this->blocks[$prototype] = [
        'opcode' => 'procedures_prototype', 'next' => null, 'parent' => $id, 'inputs' => [], 'fields' => [], 'shadow' => true, 'topLevel' => false,
        'mutation' => [
          'tagName' => 'mutation', 'children' => [], 'proccode' => (string) ($sb2[1] ?? ''),
          'argumentnames' => json_encode(array_values((array) ($sb2[2] ?? []))), 'argumentdefaults' => json_encode(array_values((array) ($sb2[3] ?? []))),
          'argumentids' => json_encode(array_map(static fn (int $i): string => 'input'.$i, array_keys(self::argumentTypes((string) ($sb2[1] ?? ''))))),
          'warp' => json_encode((bool) ($sb2[4] ?? false)),
        ],
      ];
      $this->blocks[$id]['inputs']['custom_block'] = [1, $prototype];
    }

    return $id;
  }

  /** @return array{0: mixed, 1: ?string} */
  private function field(string $name, mixed $value): array
  {
    // Scratch 2 takes a reporter in some dropdowns ("[letter of x] of sprite"); Scratch 3 cannot, so it is left empty.
    $value = \is_scalar($value) ? $value : '';

    return match ($name) {
      'VARIABLE' => [$value, $this->names['v'.$value] ?? 'stage-v-'.$value],
      'LIST' => [$value, $this->names['l'.$value] ?? 'stage-l-'.$value],
      'BROADCAST_OPTION' => [$value, $this->message((string) $value)],
      'CURRENTMENU' => ['day of week' === $value ? 'DAYOFWEEK' : strtoupper((string) $value), null],
      default => [$value, null],
    };
  }

  /**
   * A positional argument becomes a Scratch 3 input: a block (or block list) obscuring a shadow, or just the shadow.
   *
   * @return list<mixed>
   */
  private function input(string $parent, string $shadow_opcode, mixed $value): array
  {
    if ('substack' === $shadow_opcode || 'boolean' === $shadow_opcode) {
      $block = \is_array($value) ? ('substack' === $shadow_opcode ? $this->stack($value, $parent) : $this->block($value, $parent, true)) : null;

      return [2, $block];
    }

    $block = null;
    if (\is_array($value)) {
      $block = \is_array($value[0] ?? null) ? $this->stack($value, $parent) : $this->block($value, $parent, false);
    }

    $obscured = \is_array($value);
    if (isset(self::NUMBER_SHADOWS[$shadow_opcode])) {
      $shadow = [self::NUMBER_SHADOWS[$shadow_opcode], $obscured ? 10 : $value];
    } elseif ('text' === $shadow_opcode) {
      $shadow = [10, $obscured ? '' : $value];
    } elseif ('colour_picker' === $shadow_opcode) {
      $shadow = [9, $obscured ? '#990000' : self::color($value)];
    } elseif ('event_broadcast_menu' === $shadow_opcode) {
      $name = $obscured ? '' : (string) $value;
      $shadow = [11, $name, $this->message($name)];
    } else {
      $menu = match (true) {
        !$obscured && 'sensing_of_object_menu' === $shadow_opcode && 'Stage' === $value => '_stage_',
        !$obscured => $value,
        'note' === $shadow_opcode => 60,
        'music_menu_DRUM' === $shadow_opcode, 'music_menu_INSTRUMENT' === $shadow_opcode => 1,
        'sensing_of_object_menu' === $shadow_opcode => '_stage_',
        default => '',
      };
      $shadow = 'sb2-'.++$this->next_id;
      $field = ['note' => 'NOTE'][$shadow_opcode] ?? self::menuField($shadow_opcode);
      $this->blocks[$shadow] = ['opcode' => $shadow_opcode, 'next' => null, 'parent' => $parent, 'inputs' => [], 'fields' => [$field => [$menu, null]], 'shadow' => true, 'topLevel' => false];
    }

    return null === $block ? [1, $shadow] : [3, $block, $shadow];
  }

  /** Scratch 3 menu shadows name their field after the menu, e.g. looks_costume => COSTUME. */
  private static function menuField(string $shadow_opcode): string
  {
    return [
      'looks_costume' => 'COSTUME', 'looks_backdrops' => 'BACKDROP', 'sound_sounds_menu' => 'SOUND_MENU',
      'motion_pointtowards_menu' => 'TOWARDS', 'motion_goto_menu' => 'TO', 'sensing_touchingobjectmenu' => 'TOUCHINGOBJECTMENU',
      'sensing_distancetomenu' => 'DISTANCETOMENU', 'sensing_keyoptions' => 'KEY_OPTION', 'sensing_of_object_menu' => 'OBJECT',
      'control_create_clone_of_menu' => 'CLONE_OPTION', 'music_menu_DRUM' => 'DRUM', 'music_menu_INSTRUMENT' => 'INSTRUMENT',
    ][$shadow_opcode] ?? $shadow_opcode; // only video sensing menus are left, which the converter does not support
  }

  /**
   * @param array<mixed>         $sb2
   * @param array<string, mixed> $block
   *
   * @return array<string, mixed>
   */
  private static function special(string $old, array $sb2, array $block, bool $in_boolean): array
  {
    $menus = [
      'comeToFront' => ['FRONT_BACK', 'front'], 'goBackByLayers:' => ['FORWARD_BACKWARD', 'backward'],
      'backgroundIndex' => ['NUMBER_NAME', 'number'], 'sceneName' => ['NUMBER_NAME', 'name'],
      'costumeIndex' => ['NUMBER_NAME', 'number'], 'costumeName' => ['NUMBER_NAME', 'name'],
    ];
    if (isset($menus[$old])) {
      $block['fields'][$menus[$old][0]] = [$menus[$old][1], null];
    }

    if ('call' === $old) {
      $block['mutation'] = [
        'tagName' => 'mutation', 'children' => [], 'proccode' => (string) ($sb2[1] ?? ''),
        'argumentids' => json_encode(array_map(static fn (int $i): string => 'input'.$i, array_keys(self::argumentTypes((string) ($sb2[1] ?? ''))))),
      ];
    } elseif ('getParam' === $old) {
      $block['opcode'] = $in_boolean || 'b' === ($sb2[2] ?? 'r') ? 'argument_reporter_boolean' : 'argument_reporter_string_number';
    }

    return $block;
  }

  /** @return list<string> n, s or b per %-argument of a proc code */
  private static function argumentTypes(string $proccode): array
  {
    preg_match_all('/(?<!\\\)%([nbs])/', $proccode, $matches);

    return $matches[1];
  }

  private function message(string $name): string
  {
    $id = $this->messages[mb_strtolower($name)] ??= 'msg-'.mb_strtolower($name);
    $this->broadcasts[$id] ??= $name;

    return $id;
  }

  /** Scratch 2 stores colors as 0xRRGGBB integers. */
  private static function color(mixed $value): string
  {
    return \is_numeric($value) ? \sprintf('#%06x', (int) $value & 0xFFFFFF) : (string) $value;
  }
}
