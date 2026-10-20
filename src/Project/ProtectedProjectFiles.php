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
    $allowed = match ($extension) {
      'png' => ['image/png'],
      'jpg', 'jpeg' => ['image/jpeg'],
      'gif' => ['image/gif'],
      'webp' => ['image/webp'],
      'avif' => ['image/avif'],
      'bmp' => ['image/bmp', 'image/x-ms-bmp'],
      'svg' => ['image/svg+xml', 'application/xml', 'text/xml'],
      'wav' => ['audio/wav', 'audio/x-wav', 'audio/vnd.wave'],
      'mp3' => ['audio/mpeg', 'audio/mp3'],
      'ogg' => ['audio/ogg', 'application/ogg'],
      'm4a' => ['audio/mp4', 'video/mp4', 'audio/x-m4a'],
      'aac' => ['audio/aac', 'audio/x-hx-aac-adts'],
      'flac' => ['audio/flac', 'audio/x-flac'],
      'xml' => $is_metadata ? ['application/xml', 'text/xml', 'text/plain'] : [],
      default => [],
    };
    if (!is_string($mime) || !in_array($mime, $allowed, true)) {
      return null;
    }
    // XML and SVG are downloadable data, never active same-origin documents.
    $attachment = $is_metadata || 'svg' === $extension;
    $uri = '/__protected-project-resources/extract/'.$id.'/'.implode('/', array_map(rawurlencode(...), $parts));
    $response = new ProtectedProjectFileResponse($file, $uri, $attachment ? 'application/octet-stream' : $mime);
    $response->setContentDisposition($attachment ? ResponseHeaderBag::DISPOSITION_ATTACHMENT : ResponseHeaderBag::DISPOSITION_INLINE, basename($file), 'asset.'.$extension);

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
    if (!in_array((new \finfo(FILEINFO_MIME_TYPE))->file($file), ['application/zip', 'application/x-zip', 'application/x-zip-compressed'], true)) {
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
