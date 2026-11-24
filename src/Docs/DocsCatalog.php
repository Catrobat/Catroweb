<?php

declare(strict_types=1);

namespace App\Docs;

use App\Project\CodeView\CodeExportRunner;
use App\Project\CodeView\CodeTreeBuilder;
use App\Project\CodeView\CodeViewLocalizer;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * The brick and formula reference: the player build's catalog.json (player/README.md, "Brick catalog"), next to the
 * code export it is deployed with, in a language. Its strings are markers like the code export's, so it is localized
 * by the code view's own CodeViewLocalizer and CodeTreeBuilder, and its bricks look like the code view's (html()).
 * Each localized catalog is cached until a new catalog.json is deployed.
 *
 * A brick's key is its code.xml type (SetXBrick, StartScript for a script). It may be listed in several categories,
 * as in the app's brick picker; its own is the one with the most bricks of its colour.
 */
class DocsCatalog implements ResetInterface
{
  private const string TYPE_PATTERN = '/^[A-Za-z][A-Za-z0-9_]{0,99}$/';

  private const array FORMULA_KINDS = ['functions', 'sensors', 'operators'];

  /**
   * Catroid's script brick classes whose script is not named like the class with "Script" for "Brick"
   * (WhenClonedBrick: WhenClonedScript). Only for old URLs, which named bricks by their class.
   */
  private const array SCRIPT_CLASSES = [
    'WhenStartedBrick' => 'StartScript',
    'BroadcastReceiverBrick' => 'BroadcastScript',
    'WhenRaspiPinChangedBrick' => 'RaspiInterruptScript',
  ];

  /**
   * The app settings that show a category's bricks (Catroid's BrickCategoryListBuilder, CategoryBricksFactory), as the
   * strings of their titles in Catroid's settings: outer screen first. The catalog lists every brick with every setting
   * on. A brick listed in another category too is shown without the setting, except for Device, which lists the
   * bricks of every switched-on category.
   */
  private const array CATEGORY_SETTINGS = [
    'category_arduino' => ['preference_title_enable_arduino_bricks'],
    'category_assertions' => ['preference_title_enable_test_bricks'],
    'category_cast' => ['preference_title_cast_feature_globally_enabled'],
    'category_drone' => ['preference_title_enable_quadcopter_bricks'],
    'category_embroidery' => ['preference_title_enable_embroidery_bricks'],
    'category_jumping_sumo' => ['preference_title_enable_jumpingsumo_bricks'],
    'category_laser' => ['preference_title_enable_plot_bricks'],
    'category_lego_ev3' => ['preference_title_enable_mindstorms_ev3_bricks'],
    'category_lego_nxt' => ['preference_title_enable_mindstorms_nxt_bricks'],
    'category_phiro' => ['preference_title_enable_phiro_bricks'],
    'category_plot' => ['preference_title_enable_plot_bricks'],
    'category_raspi' => ['preference_title_enable_raspi_bricks'],
  ];

  /** The gated categories whose bricks Device also lists when they are on (CategoryBricksFactory setupDeviceCategoryList). */
  private const array DEVICE_APPENDS = ['category_arduino', 'category_cast', 'category_drone', 'category_embroidery', 'category_jumping_sumo', 'category_lego_ev3', 'category_lego_nxt', 'category_phiro', 'category_raspi'];

  /** Bricks of the other categories that a setting shows (CategoryBricksFactory), as in CATEGORY_SETTINGS. */
  private const array BRICK_SETTINGS = [
    'AskSpeechBrick' => ['preference_title_ai', 'preference_title_ai_speech_recognition'],
    'PhiroIfLogicBeginBrick' => ['preference_title_enable_phiro_bricks'],
    'PhiroMotorMoveBackwardBrick' => ['preference_title_enable_phiro_bricks'],
    'PhiroMotorMoveForwardBrick' => ['preference_title_enable_phiro_bricks'],
    'PhiroMotorStopBrick' => ['preference_title_enable_phiro_bricks'],
    'PhiroPlayToneBrick' => ['preference_title_enable_phiro_bricks'],
    'PhiroRGBLightBrick' => ['preference_title_enable_phiro_bricks'],
    'SetListeningLanguageBrick' => ['preference_title_ai', 'preference_title_ai_speech_recognition'],
    'SetNfcTagBrick' => ['preference_title_enable_nfc_bricks'],
    'SpeakAndWaitBrick' => ['preference_title_ai', 'preference_title_ai_speech_synthetization'],
    'SpeakBrick' => ['preference_title_ai', 'preference_title_ai_speech_synthetization'],
    'StartListeningBrick' => ['preference_title_ai', 'preference_title_ai_speech_recognition'],
    'WhenNfcScript' => ['preference_title_enable_nfc_bricks'],
    'WriteEmbroideryToFileBrick' => ['preference_title_enable_embroidery_bricks'],
  ];

  /** @var array<string, array{categories: list<array{key: string, name: string, color: string, bricks: list<array<string, mixed>>}>, formula: array<string, list<array{name: string, label: string|null}>>}> */
  private array $localized = [];

  /** @var array{classes: list<string>, aliases: array<string, string>}|null */
  private ?array $classes = null;

  public function __construct(
    private readonly CodeExportRunner $runner,
    private readonly CodeViewLocalizer $localizer,
    private readonly CodeTreeBuilder $tree,
    private readonly CacheInterface $cache,
    #[Autowire('%kernel.project_dir%/assets/Project/CodeViewCategories.json')]
    private readonly string $categories_file,
  ) {
  }

  /**
   * Every category with its bricks and the formula elements, localized.
   *
   * @return array{categories: list<array{key: string, name: string, color: string, bricks: list<array<string, mixed>>}>, formula: array<string, list<array{name: string, label: string|null}>>}
   */
  public function catalog(string $locale): array
  {
    // Catroid's translations are per Android locale: de, de_AT and de-DE share one entry, and the key stays bounded.
    $android = $this->localizer->androidLocale($locale);
    $file = $this->runner->getExportDir().'/catalog.json';
    clearstatcache(true, $file);
    $version = is_file($file) ? filemtime($file).'-'.filesize($file) : 'none';

    $key = 'docs_catalog.'.hash('xxh128', $file.'|'.$version).'.'.$android;

    // Keyed by version too: a worker serving many requests sees a newly deployed catalog.
    return $this->localized[$key] ??= $this->cache->get(
      $key,
      fn (): array => $this->localize($this->read($file), str_replace('-r', '_', $android)),
    );
  }

  #[\Override]
  public function reset(): void
  {
    $this->localized = [];
  }

  /**
   * The localized brick, the categories that list it and the app setting that shows it (null: always shown), or null.
   *
   * @return array{brick: array<string, mixed>, categories: list<array{key: string, name: string, color: string}>, setting: string|null}|null
   */
  public function brick(string $type, string $locale): ?array
  {
    $listed = array_values(array_filter(
      $this->catalog($locale)['categories'],
      static fn (array $category): bool => \in_array($type, array_column($category['bricks'], 'type'), true),
    ));
    if ([] === $listed) {
      return null;
    }
    $found = array_values(array_filter($listed[0]['bricks'], static fn (array $brick): bool => $brick['type'] === $type))[0];
    // Its own category is the one with the most bricks of its colour: SetXBrick is a motion brick, also listed under
    // device and embroidery.
    $own = $this->colorClass((string) $found['category']);
    $score = fn (array $category): int => \count(array_filter($category['bricks'], fn (array $brick): bool => $this->colorClass((string) $brick['category']) === $own));
    usort($listed, static fn (array $a, array $b): int => $score($b) <=> $score($a));

    return [
      'brick' => $found,
      'categories' => array_map(static fn (array $category): array => ['key' => $category['key'], 'name' => $category['name'], 'color' => $category['color']], $listed),
      'setting' => $this->setting($type, array_column($listed, 'key'), $locale),
    ];
  }

  /**
   * The title of the app setting that shows this brick ("Lego Mindstorms NXT extension"), or null if it is always
   * shown or its title is not in the deployed strings.
   *
   * @param list<string> $categories the keys of the categories listing it, its own first
   */
  private function setting(string $type, array $categories, string $locale): ?string
  {
    $names = self::BRICK_SETTINGS[$type] ?? null;
    if (null === $names) {
      $gated = array_values(array_intersect($categories, array_keys(self::CATEGORY_SETTINGS)));
      // Device lists the bricks of the extensions it appends, so it counts as gated only for those.
      $device = [] !== array_intersect($gated, self::DEVICE_APPENDS) ? ['category_device'] : [];
      if ([] === $gated || [] !== array_diff($categories, $gated, $device)) {
        return null;
      }
      $names = self::CATEGORY_SETTINGS[$gated[0]];
    }
    $titles = [];
    foreach ($names as $name) {
      $title = $this->localizer->localize("\x01s:".$name."\x02", $locale);
      if ($title === $name) {
        return null; // not in this export's strings: no hint rather than a string's name
      }
      $titles[] = $title;
    }

    return implode(' › ', $titles);
  }

  /**
   * @return array{name: string, label: string|null, kind: string}|null
   */
  public function formula(string $name, string $locale): ?array
  {
    foreach ($this->catalog($locale)['formula'] as $kind => $elements) {
      foreach ($elements as $element) {
        if ($element['name'] === $name) {
          return $element + ['kind' => $kind];
        }
      }
    }

    return null;
  }

  /**
   * Every brick type, in catalog order.
   *
   * @return list<string>
   */
  public function types(): array
  {
    $types = [];
    foreach ($this->catalog('en')['categories'] as $category) {
      foreach ($category['bricks'] as $brick) {
        $types[(string) $brick['type']] = true;
      }
    }

    return array_keys($types);
  }

  /** The type of a brick named by its Catroid class (old wiki URLs: WhenStartedBrick is StartScript), or null. */
  public function typeOfClass(string $class): ?string
  {
    $types = $this->types();
    foreach ([$class, self::SCRIPT_CLASSES[$class] ?? null, preg_replace('/Brick$/', 'Script', $class)] as $type) {
      if (null !== $type && \in_array($type, $types, true)) {
        return $type;
      }
    }

    return null;
  }

  /**
   * A localized brick as the code view shows it (assets/Project/CodeViewInline.js, renderBrick and renderScript), styled
   * by CodeViewInline.css.
   *
   * @param array<string, mixed> $brick
   */
  public function html(array $brick): string
  {
    $type = (string) ($brick['type'] ?? '');
    $text = $this->partsHtml($brick['parts'] ?? null);
    $text = '' === $text ? htmlspecialchars($type, ENT_QUOTES) : $text;
    if (str_ends_with($type, 'Script')) {
      $text = '<strong>'.$text.'</strong>';
    }
    $sections = '';
    foreach (\is_array($brick['sections'] ?? null) ? array_values($brick['sections']) : [] as $i => $section) {
      if ($i > 0) {
        $sections .= '<div class="cv-branch-label">'.$this->partsHtml($section['parts'] ?? null).'</div>';
      }
      $bricks = \is_array($section['bricks'] ?? null) ? $section['bricks'] : [];
      if ([] !== $bricks) {
        $sections .= '<div class="cv-block__children">'.implode('', array_map($this->html(...), $bricks)).'</div>';
      }
    }

    return '<div class="cv-block cv-block--'.$this->colorClass((string) ($brick['category'] ?? 'unknown')).'">'.$text.$sections.'</div>';
  }

  /** The cv-block--* class of one of Catroid's brick categories (its layout style). */
  public function colorClass(string $category): string
  {
    $this->classes ??= json_decode((string) file_get_contents($this->categories_file), true, 4, JSON_THROW_ON_ERROR);
    $name = $this->classes['aliases'][$category] ?? $category;

    return \in_array($name, $this->classes['classes'], true) ? $name : 'unknown';
  }

  /**
   * A brick's text without formatting, for titles: "Set x to 100".
   *
   * @param array<string, mixed> $brick
   */
  public function title(array $brick): string
  {
    $texts = array_map(self::partText(...), \is_array($brick['parts'] ?? null) ? $brick['parts'] : []);
    $title = trim(implode(' ', array_filter($texts, static fn (string $text): bool => '' !== $text)));

    return '' === $title ? (string) ($brick['type'] ?? '') : $title;
  }

  /** @param array<string, mixed> $part */
  private static function partText(array $part): string
  {
    $segments = \is_array($part['segments'] ?? null) ? $part['segments'] : [];

    return [] === $segments
      ? (string) ($part['text'] ?? '')
      : implode('', array_map(static fn (array $segment): string => (string) ($segment['text'] ?? ''), $segments));
  }

  private function partsHtml(mixed $parts): string
  {
    $html = [];
    foreach (\is_array($parts) ? $parts : [] as $part) {
      $text = htmlspecialchars(self::partText($part), ENT_QUOTES);
      $html[] = match ($part['kind'] ?? null) {
        'formula' => '<span class="cv-field">'.$text.'</span>',
        'choice' => '<span class="cv-field cv-field--choice">'.$text.'</span>',
        default => $text,
      };
    }

    return implode(' ', $html);
  }

  /**
   * @return array<string, mixed>
   */
  private function read(string $file): array
  {
    if (!is_file($file)) {
      return []; // no player build deployed: an empty reference
    }

    try {
      $catalog = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
    } catch (\JsonException) {
      return [];
    }

    return \is_array($catalog) ? $catalog : [];
  }

  /**
   * @param array<string, mixed> $catalog
   *
   * @return array{categories: list<array{key: string, name: string, color: string, bricks: list<array<string, mixed>>}>, formula: array<string, list<array{name: string, label: string|null}>>}
   */
  private function localize(array $catalog, string $locale): array
  {
    $categories = [];
    foreach (\is_array($catalog['categories'] ?? null) ? $catalog['categories'] : [] as $category) {
      $bricks = [];
      foreach (\is_array($category['bricks'] ?? null) ? $category['bricks'] : [] as $brick) {
        if (\is_array($brick) && \is_string($brick['type'] ?? null) && 1 === preg_match(self::TYPE_PATTERN, $brick['type'])) {
          $bricks[] = $this->tree->brick($brick, $locale);
        }
      }
      if ([] === $bricks || !\is_string($category['key'] ?? null)) {
        continue;
      }
      // A category's colour is that of most of its bricks (category_device lists bricks of many styles).
      $colors = array_count_values(array_map(fn (array $brick): string => $this->colorClass((string) $brick['category']), $bricks));
      arsort($colors);
      $categories[] = [
        'key' => $category['key'],
        'name' => $this->localizer->localize((string) ($category['name'] ?? $category['key']), $locale),
        'color' => array_key_first($colors),
        'bricks' => $bricks,
      ];
    }

    $formula = [];
    foreach (self::FORMULA_KINDS as $kind) {
      $formula[$kind] = [];
      foreach (\is_array($catalog['formula'][$kind] ?? null) ? $catalog['formula'][$kind] : [] as $element) {
        if (\is_array($element) && \is_string($element['name'] ?? null) && 1 === preg_match(self::TYPE_PATTERN, $element['name'])) {
          $label = \is_string($element['label'] ?? null) ? $this->localizer->localize($element['label'], $locale) : '';
          $formula[$kind][] = ['name' => $element['name'], 'label' => '' === $label ? null : $label];
        }
      }
    }

    return ['categories' => $categories, 'formula' => $formula];
  }
}
