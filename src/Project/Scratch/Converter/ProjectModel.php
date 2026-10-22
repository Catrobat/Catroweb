<?php

declare(strict_types=1);

namespace App\Project\Scratch\Converter;

/**
 * What the block converter needs to know about the whole project: sprites, their looks/sounds/variables, and
 * the helper state it asks for along the way (cross-sprite values, clone requests, backdrop stepping).
 *
 * Every look, sound, variable and list is written in full wherever it is used and tagged with a stable key;
 * XStreamReferences turns all but the first occurrence into back-references.
 */
final class ProjectModel
{
  public const string STAGE = 'stage';

  /** @var array<string, array<string, mixed>> sprite key => Scratch target */
  private array $targets = [];

  /** @var array<string, string> Scratch sprite name => sprite key */
  private array $keys_by_name = [];

  /** @var array<string, string> sprite key => Pocket Code object name */
  private array $names = [];

  /** @var array<string, list<array{name: string, file: string}>> */
  private array $looks = [];

  /** @var array<string, list<array{name: string, file: string}>> */
  private array $sounds = [];

  /** @var array<string, string> variable/list key => device key UUID */
  private array $device_keys = [];

  /** @var array<string, array{sprite: ?string, formula: Formula}> program variable name => value source */
  private array $shadows = [];

  /** @var array<string, true> sprite keys that receive "create clone of" broadcasts */
  private array $clone_requests = [];

  private bool $uses_answer = false;

  /** @var array<string, int> */
  private array $unsupported = [];

  public function __construct(private readonly \DOMDocument $doc)
  {
  }

  /** @param array<string, mixed> $target */
  public function addTarget(string $key, array $target, string $name): void
  {
    $this->targets[$key] = $target;
    $this->names[$key] = $name;
    if (!($target['isStage'] ?? false)) {
      $this->keys_by_name[(string) $target['name']] = $key;
    }
  }

  /** @param list<array{name: string, file: string}> $looks */
  public function setLooks(string $key, array $looks): void
  {
    $this->looks[$key] = $looks;
  }

  /** @param list<array{name: string, file: string}> $sounds */
  public function setSounds(string $key, array $sounds): void
  {
    $this->sounds[$key] = $sounds;
  }

  public function isStage(string $key): bool
  {
    return self::STAGE === $key;
  }

  public function objectName(string $key): string
  {
    return $this->names[$key];
  }

  // ---- looks and sounds ----

  public function lookElement(string $key, int $index): \DOMElement
  {
    $look = $this->looks[$key][$index];
    $element = $this->doc->createElement('look');
    $element->setAttribute(XStreamReferences::KEY, 'look:'.$key.':'.$index);
    $element->setAttribute('fileName', $look['file']);
    $element->setAttribute('name', $look['name']);
    $element->appendChild($this->doc->createElement('isWebRequest', 'false'));
    $element->appendChild($this->doc->createElement('valid', 'true'));

    return $element;
  }

  public function soundElement(string $key, int $index): \DOMElement
  {
    $sound = $this->sounds[$key][$index];
    $element = $this->doc->createElement('sound');
    $element->setAttribute(XStreamReferences::KEY, 'sound:'.$key.':'.$index);
    $element->setAttribute('fileName', $sound['file']);
    $element->setAttribute('name', $sound['name']);
    $element->appendChild($this->doc->createElement('midiFile', 'false'));

    return $element;
  }

  public function lookCount(string $key): int
  {
    return \count($this->looks[$key] ?? []);
  }

  public function soundCount(string $key): int
  {
    return \count($this->sounds[$key] ?? []);
  }

  public function look(string $key, string $name): ?\DOMElement
  {
    $index = self::indexByName($this->looks[$key] ?? [], $name);

    return null === $index ? null : $this->lookElement($key, $index);
  }

  public function backdropLook(string $name): ?\DOMElement
  {
    return $this->look(self::STAGE, $name);
  }

  public function backdropCount(): int
  {
    return max(1, $this->lookCount(self::STAGE));
  }

  public function sound(string $key, string $name): ?\DOMElement
  {
    $index = self::indexByName($this->sounds[$key] ?? [], $name);

    return null === $index ? null : $this->soundElement($key, $index);
  }

  // ---- variables and lists ----

  /**
   * A variable used by a brick. Sprite-local if the sprite owns it (by id, then by name), otherwise global.
   */
  public function variableElement(?string $sprite_key, string $name, ?string $id = null): \DOMElement
  {
    if (BlockConverter::ANSWER === $name && null === $sprite_key) {
      $this->uses_answer = true;
    }

    $owner = $this->owner($sprite_key, 'variables', $name, $id);
    $key = 'var:'.$owner.':'.$name;

    $element = $this->doc->createElement('userVariable');
    $element->setAttribute(XStreamReferences::KEY, $key);
    $element->setAttribute('type', 'UserVariable');
    $element->setAttribute('serialization', 'custom');
    $default = $element->appendChild($this->doc->createElement('userVariable'))->appendChild($this->doc->createElement('default'));
    $default->appendChild($this->doc->createElement('initialIndex', '-1'));
    $default->appendChild($this->doc->createElement('deviceValueKey', $this->deviceKey($key)));
    $default->appendChild($this->doc->createElement('name'))->appendChild($this->doc->createTextNode($name));

    return $element;
  }

  public function listElement(?string $sprite_key, string $name, ?string $id = null): \DOMElement
  {
    $owner = $this->owner($sprite_key, 'lists', $name, $id);
    $key = 'list:'.$owner.':'.$name;

    $element = $this->doc->createElement('userList');
    $element->setAttribute(XStreamReferences::KEY, $key);
    $element->appendChild($this->doc->createElement('deviceListKey', $this->deviceKey($key)));
    $element->appendChild($this->doc->createElement('initialIndex', '-1'));
    $element->appendChild($this->doc->createElement('name'))->appendChild($this->doc->createTextNode($name));

    return $element;
  }

  /** @return list<string> names of the program-wide variables, including the ones the converter adds */
  public function programVariableNames(): array
  {
    $names = array_map(static fn (array $v): string => (string) $v[0], array_values($this->targets[self::STAGE]['variables'] ?? []));
    if ($this->uses_answer) {
      $names[] = BlockConverter::ANSWER;
    }

    $counters = array_map(fn (string $key): string => self::cloneRequestNames($this->names[$key])['counter'], array_keys($this->clone_requests));

    return array_values(array_unique([...$names, ...array_keys($this->shadows), ...$counters]));
  }

  // ---- helper state requested by the block converter ----

  /**
   * Pocket Code formulas can't read another sprite's position, costume or local variables. The owner copies the
   * value into a program variable every frame instead; this returns that variable (null for unknown sprites).
   */
  public function shadowVariable(?string $sprite_name, string $property): ?Formula
  {
    $key = null === $sprite_name ? self::STAGE : ($this->keys_by_name[$sprite_name] ?? null);
    if (null === $key) {
      return null;
    }

    $source = match (strtolower($property)) {
      'x position' => Formula::sensor('OBJECT_X'),
      'y position' => Formula::sensor('OBJECT_Y'),
      'direction' => Formula::sensor('MOTION_DIRECTION'),
      'costume #' => Formula::sensor('OBJECT_LOOK_NUMBER'),
      'costume name' => Formula::sensor('OBJECT_LOOK_NAME'),
      'size' => Formula::sensor('OBJECT_SIZE'),
      'backdrop #' => Formula::sensor('OBJECT_BACKGROUND_NUMBER'),
      'backdrop name' => Formula::sensor('OBJECT_BACKGROUND_NAME'),
      'volume' => Formula::number(100),
      default => self::owns($this->targets[$key]['variables'] ?? [], $property, null) ? Formula::variable($property) : null,
    };
    if (null === $source) {
      return null;
    }

    $name = '__scratch '.$this->names[$key].' '.$property;
    $this->shadows[$name] = ['sprite' => $key, 'formula' => $source];

    return Formula::variable($name);
  }

  /** @return array<string, array{sprite: ?string, formula: Formula}> */
  public function shadows(): array
  {
    return $this->shadows;
  }

  /**
   * "create clone of <sprite>" becomes a counted request: the requester increments a counter and broadcasts,
   * the original sprite clones itself until the counter is back at zero.
   *
   * @return array{message: string, counter: string}|null
   */
  public function cloneRequest(string $sprite_name): ?array
  {
    $key = $this->keys_by_name[$sprite_name] ?? null;
    if (null === $key) {
      return null;
    }

    $this->clone_requests[$key] = true;

    return self::cloneRequestNames($this->names[$key]);
  }

  /** @return array{message: string, counter: string} */
  public static function cloneRequestNames(string $object_name): array
  {
    return ['message' => '__scratch clone '.$object_name, 'counter' => '__scratch clone requests '.$object_name];
  }

  public function receivesCloneRequests(string $key): bool
  {
    return isset($this->clone_requests[$key]);
  }

  public function unsupported(string $opcode): void
  {
    $this->unsupported[$opcode] = ($this->unsupported[$opcode] ?? 0) + 1;
  }

  /** @return array<string, int> */
  public function unsupportedOpcodes(): array
  {
    return $this->unsupported;
  }

  // ---- internals ----

  /** Stage variables and lists are program-wide; a sprite's own ones are local to it. */
  private function owner(?string $sprite_key, string $kind, string $name, ?string $id): string
  {
    if (null === $sprite_key || self::STAGE === $sprite_key || !self::owns($this->targets[$sprite_key][$kind] ?? [], $name, $id)) {
      return 'program';
    }

    return $sprite_key;
  }

  private function deviceKey(string $key): string
  {
    return $this->device_keys[$key] ??= BlockConverter::uuid();
  }

  /** @param array<string, mixed> $entries Scratch variables/lists: id => [name, value] */
  private static function owns(array $entries, string $name, ?string $id): bool
  {
    if (null !== $id && isset($entries[$id])) {
      return true;
    }

    foreach ($entries as $entry) {
      if (\is_array($entry) && ($entry[0] ?? null) === $name) {
        return true;
      }
    }

    return false;
  }

  /** @param list<array{name: string, file: string}> $items */
  private static function indexByName(array $items, string $name): ?int
  {
    foreach ($items as $i => $item) {
      if ($item['name'] === $name) {
        return $i;
      }
    }

    return null;
  }
}
