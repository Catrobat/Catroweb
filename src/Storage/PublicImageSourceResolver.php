<?php

declare(strict_types=1);

namespace App\Storage;

/** Restricts the legacy public converter to explicitly public raster-image directories. */
final class PublicImageSourceResolver
{
  private const array PUBLIC_ROOTS = [
    'images', 'build/assets',
    'resources/images', 'resources/screenshots', 'resources/thumbnails',
    'resources/featured', 'resources/example', 'resources/media',
    'resources/templates/screenshots', 'resources/templates/thumbnails',
    'resources_test/images', 'resources_test/screenshots', 'resources_test/thumbnails',
    'resources_test/featured', 'resources_test/example', 'resources_test/media',
    'resources_test/templates/screenshots', 'resources_test/templates/thumbnails',
  ];

  private const array PROTECTED_ROOTS = [
    'resources/programs', 'resources/projects', 'resources/extract', 'resources/assets',
    'resources_test/programs', 'resources_test/projects', 'resources_test/extract', 'resources_test/assets',
  ];

  /** @return array{source: string, destination: string}|null */
  public function resolve(string $public_dir, string $source): ?array
  {
    if ('' === $source || !str_starts_with($source, '/') || str_contains($source, '\\') || str_contains($source, '%')
      || preg_match('/[\x00-\x1f\x7f]/', $source)
      || preg_match('~(?:^|/)\.{1,2}(?:/|$)~', $source)) {
      return null;
    }

    $public_dir = rtrim($public_dir, '/');
    $real_source = realpath($source);
    if (false === $real_source || !is_file($real_source) || !is_readable($real_source)) {
      return null;
    }

    foreach (self::PROTECTED_ROOTS as $relative_root) {
      $protected_root = realpath($public_dir.'/'.$relative_root);
      if (false !== $protected_root && $this->within($real_source, $protected_root)) {
        return null;
      }
    }

    $allowed = false;
    foreach (self::PUBLIC_ROOTS as $relative_root) {
      $root = $public_dir.'/'.$relative_root;
      $real_root = realpath($root);
      if (false !== $real_root && $this->within($real_source, $real_root)) {
        $allowed = true;
        break;
      }
    }
    if (!$allowed) {
      return null;
    }

    $extension = strtolower(pathinfo($real_source, PATHINFO_EXTENSION));
    $expected_mime = match ($extension) {
      'jpg', 'jpeg' => 'image/jpeg',
      'png' => 'image/png',
      default => null,
    };
    if (null === $expected_mime || $expected_mime !== (new \finfo(FILEINFO_MIME_TYPE))->file($real_source)) {
      return null;
    }

    $destination = substr($real_source, 0, -(strlen($extension) + 1)).'.webp';
    if (is_link($destination) || (file_exists($destination) && (!is_file($destination) || realpath($destination) !== $destination))) {
      return null;
    }

    return ['source' => $real_source, 'destination' => $destination];
  }

  private function within(string $path, string $root): bool
  {
    return str_starts_with($path, rtrim($root, '/').'/');
  }
}
