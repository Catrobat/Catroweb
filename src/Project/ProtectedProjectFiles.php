<?php

declare(strict_types=1);

namespace App\Project;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/** Resolves files only after the caller has applied the relevant project access policy. */
final class ProtectedProjectFiles
{
  public function __construct(
    #[Autowire('%catrobat.file.extract.dir%')]
    private readonly string $extract_dir,
    #[Autowire('%catrobat.file.storage.dir%')]
    private readonly string $archive_dir,
  ) {
  }

  public function asset(string $id, string $path): ?ProtectedProjectFileResponse
  {
    if (!$this->safeId($id) || !$this->safePath($path)) {
      return null;
    }
    $parts = explode('/', $path);
    $is_metadata = 'code.xml' === $path;
    $is_screenshot = in_array($path, ['manual_screenshot.png', 'automatic_screenshot.png', 'screenshot.png'], true);
    $is_asset = (2 === count($parts) || 3 === count($parts)) && in_array($parts[count($parts) - 2], ['images', 'sounds'], true);
    if (!$is_metadata && !$is_screenshot && !$is_asset) {
      return null;
    }
    $root = realpath($this->extract_dir);
    if (false === $root) {
      return null;
    }
    $project_root = $root.'/'.$id;
    if (is_link($project_root) || !is_dir($project_root) || realpath($project_root) !== $project_root) {
      return null;
    }
    $file = $project_root;
    foreach ($parts as $part) {
      $file .= '/'.$part;
      if (is_link($file)) {
        return null;
      }
    }
    if (!is_file($file) || !is_readable($file) || realpath($file) !== $file) {
      return null;
    }
    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file);
    $is_sound = $is_asset && 'sounds' === $parts[count($parts) - 2];
    if ($is_sound) {
      // Catroid preserves imported names and records MPEG-4/AAC as extensionless "recording".
      // Canonical media types keep container aliases inert without trusting the filename.
      $mime = match ($mime) {
        'audio/wav', 'audio/x-wav', 'audio/vnd.wave' => 'audio/wav',
        'audio/mpeg', 'audio/mp3' => 'audio/mpeg',
        'audio/ogg', 'application/ogg' => 'audio/ogg',
        'audio/mp4', 'video/mp4', 'audio/x-m4a' => 'audio/mp4',
        'audio/aac', 'audio/x-hx-aac-adts' => 'audio/aac',
        'audio/flac', 'audio/x-flac' => 'audio/flac',
        default => null,
      };
      if (null === $mime) {
        return null;
      }
    } else {
      $allowed = match ($extension) {
        'png' => ['image/png'],
        'jpg', 'jpeg' => ['image/jpeg'],
        'gif' => ['image/gif'],
        // Catroid converts imported WebP to PNG while retaining its original filename.
        'webp' => ['image/webp', 'image/png'],
        'avif' => ['image/avif'],
        'bmp' => ['image/bmp', 'image/x-ms-bmp'],
        // Imported raster looks may have no extension; active documents are excluded.
        '' => $is_asset ? ['image/png', 'image/jpeg', 'image/gif', 'image/webp', 'image/avif', 'image/bmp', 'image/x-ms-bmp'] : [],
        'svg' => ['image/svg+xml', 'application/xml', 'text/xml'],
        'xml' => $is_metadata ? ['application/xml', 'text/xml', 'text/plain'] : [],
        default => [],
      };
      if (!is_string($mime) || !in_array($mime, $allowed, true)) {
        return null;
      }
      if ('image/x-ms-bmp' === $mime) {
        $mime = 'image/bmp';
      }
    }
    // SVG previews retain an image MIME but are sandboxed even on direct navigation.
    $is_svg = !$is_sound && 'svg' === $extension;
    $attachment = $is_metadata;
    $uri = '/__protected-project-resources/extract/'.$id.'/'.implode('/', array_map(rawurlencode(...), $parts));
    $response = new ProtectedProjectFileResponse($file, $uri, $attachment ? 'application/octet-stream' : ($is_svg ? 'image/svg+xml' : $mime));
    if ($is_svg) {
      $response->headers->set('Content-Security-Policy', "sandbox; default-src 'none'; style-src 'unsafe-inline'; img-src data:");
    }
    $response->setContentDisposition($attachment ? ResponseHeaderBag::DISPOSITION_ATTACHMENT : ResponseHeaderBag::DISPOSITION_INLINE, basename($file), '' === $extension ? 'asset' : 'asset.'.$extension);

    return $response;
  }

  public function archive(string $id, BinaryFileResponse $original): ?ProtectedProjectFileResponse
  {
    if (!$this->safeId($id)) {
      return null;
    }
    $root = realpath($this->archive_dir);
    if (false === $root) {
      return null;
    }
    $file = $root.'/'.$id.'.catrobat';
    if (is_link($file) || !is_file($file) || !is_readable($file) || realpath($file) !== $file || $original->getFile()->getRealPath() !== $file) {
      return null;
    }
    $response = new ProtectedProjectFileResponse($file, '/__protected-project-resources/programs/'.$id.'.catrobat', 'application/zip');
    $disposition = $original->headers->get('Content-Disposition');
    if (null !== $disposition) {
      $response->headers->set('Content-Disposition', $disposition);
    } else {
      $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $id.'.catrobat');
    }

    return $response;
  }

  private function safeId(string $id): bool
  {
    return 1 === preg_match('/^[a-zA-Z0-9-]+$/D', $id);
  }

  private function safePath(string $path): bool
  {
    if ('' === $path || str_contains($path, '\\') || str_contains($path, '%') || preg_match('/[\x00-\x1f\x7f]/', $path)) {
      return false;
    }
    foreach (explode('/', $path) as $part) {
      if ('' === $part || '.' === $part || '..' === $part) {
        return false;
      }
    }

    return true;
  }
}
