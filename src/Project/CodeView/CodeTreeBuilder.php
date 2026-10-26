<?php

declare(strict_types=1);

namespace App\Project\CodeView;

use App\DB\Entity\Project\Project;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The code view of a project (GET /projects/{id}/code): scenes > objects > scripts > bricks, each brick as the app
 * shows it, in the viewer's language. It comes from Catroid's own code through the player's code export
 * (CodeViewExportStore); this only puts in the translations and the URLs of looks and sounds.
 */
class CodeTreeBuilder
{
  public function __construct(
    private readonly CodeViewExportStore $store,
    private readonly CodeViewLocalizer $localizer,
    private readonly RequestStack $request_stack,
  ) {
  }

  /**
   * @return array{scenes: list<array<string, mixed>>}
   *
   * @throws CodeTreeBuildException when the project's code cannot be shown
   */
  public function buildCodeTree(Project $project, ?string $locale = null): array
  {
    $export = $this->store->get($project);
    if (null === $export) {
      throw new CodeTreeBuildException('Could not load project files');
    }
    if (isset($export['error']) || !is_array($export['scenes'] ?? null)) {
      throw new CodeTreeBuildException('Cannot show this project\'s code: '.(is_string($export['error'] ?? null) ? $export['error'] : 'no scenes'));
    }

    $locale ??= $this->request_stack->getCurrentRequest()?->getLocale() ?? 'en';
    $files = $this->filesUrl((string) $project->getId());
    $in_scene_folders = (bool) ($export['files_in_scene_folders'] ?? true);

    $scenes = [];
    foreach ($export['scenes'] as $scene) {
      $name = (string) ($scene['name'] ?? '');
      $base = $files.($in_scene_folders ? rawurlencode($name).'/' : '');
      $scenes[] = [
        'name' => $this->localizer->localize($name, $locale),
        'objects' => array_map(fn (array $object): array => $this->object($object, $base, $locale), $this->list($scene['objects'] ?? null)),
      ];
    }

    return ['scenes' => $scenes];
  }

  /**
   * @param array<string, mixed> $object
   *
   * @return array<string, mixed>
   */
  private function object(array $object, string $base, string $locale): array
  {
    $group = $this->list($object['group'] ?? null);

    return [
      'name' => $this->localizer->localize((string) ($object['name'] ?? ''), $locale),
      'is_group' => isset($object['group']),
      'scripts' => array_map(fn (array $script): array => $this->brick($script, $locale), $this->list($object['scripts'] ?? null)),
      'looks' => $this->assets($this->list($object['looks'] ?? null), $base.'images/', $locale),
      'sounds' => $this->assets($this->list($object['sounds'] ?? null), $base.'sounds/', $locale),
      'children' => isset($object['group']) ? array_map(fn (array $child): array => $this->object($child, $base, $locale), $group) : null,
    ];
  }

  /**
   * @param list<array<string, mixed>> $assets
   *
   * @return list<array{name: string, url: string|null}>
   */
  private function assets(array $assets, string $base, string $locale): array
  {
    return array_map(fn (array $asset): array => [
      'name' => $this->localizer->localize((string) ($asset['name'] ?? ''), $locale),
      'url' => '' === (string) ($asset['file'] ?? '') ? null : $base.rawurlencode((string) $asset['file']),
    ], $assets);
  }

  /**
   * A script or brick: its parts, the bricks in it (a script's) and its sections (an if's else and end, a loop's end).
   *
   * @param array<string, mixed> $brick
   *
   * @return array<string, mixed>
   */
  private function brick(array $brick, string $locale): array
  {
    $out = [
      'type' => (string) ($brick['type'] ?? ''),
      'category' => (string) ($brick['category'] ?? 'unknown'),
      'commented_out' => (bool) ($brick['commented_out'] ?? false),
      'parts' => $this->parts($brick['parts'] ?? null, $locale),
      'bricks' => null,
      'sections' => null,
    ];
    if (isset($brick['bricks'])) {
      $out['bricks'] = array_map(fn (array $b): array => $this->brick($b, $locale), $this->list($brick['bricks']));
    }
    if (isset($brick['sections'])) {
      $out['sections'] = array_map(fn (array $section): array => [
        'parts' => $this->parts($section['parts'] ?? null, $locale),
        'bricks' => array_map(fn (array $b): array => $this->brick($b, $locale), $this->list($section['bricks'] ?? null)),
      ], $this->list($brick['sections']));
    }

    return $out;
  }

  /**
   * @return list<array{kind: string, text: string}>
   */
  private function parts(mixed $parts, string $locale): array
  {
    return array_map(fn (array $part): array => [
      'kind' => in_array($part['kind'] ?? null, ['label', 'formula', 'choice'], true) ? $part['kind'] : 'label',
      'text' => $this->localizer->localize((string) ($part['text'] ?? ''), $locale),
    ], $this->list($parts));
  }

  /**
   * @return list<array<string, mixed>>
   */
  private function list(mixed $value): array
  {
    return is_array($value) ? array_values(array_filter($value, is_array(...))) : [];
  }

  private function filesUrl(string $project_id): string
  {
    $base_url = trim($this->request_stack->getCurrentRequest()?->getBaseUrl() ?? '', '/');

    return '/'.('' === $base_url ? '' : $base_url.'/').'project-files/'.$project_id.'/assets/';
  }
}
