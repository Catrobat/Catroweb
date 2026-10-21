<?php

declare(strict_types=1);

namespace App\Project;

use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

/** Offloads only when a server-owned FastCGI parameter enables the internal location. */
#[Exclude]
final class ProtectedProjectFileResponse extends BinaryFileResponse
{
  public function __construct(string $file, private readonly string $internal_uri, string $mime, private readonly bool $allow_acceleration = true)
  {
    parent::__construct($file, headers: [
      'Content-Type' => $mime,
      'Cache-Control' => 'private, no-store',
      'X-Content-Type-Options' => 'nosniff',
    ], public: false, autoLastModified: false);
  }

  #[\Override]
  public function prepare(Request $request): static
  {
    $safe_request = clone $request;
    // Client-supplied sendfile headers must never choose a filesystem mapping.
    $safe_request->headers->remove('X-Sendfile-Type');
    $safe_request->headers->remove('X-Accel-Mapping');
    $accelerated = $this->allow_acceleration && '1' === $request->server->get('PROJECT_FILE_ACCEL');
    if ($accelerated) {
      // nginx applies the original range request to the internal file itself.
      $safe_request->headers->remove('Range');
      $safe_request->headers->remove('If-Range');
    }
    parent::prepare($safe_request);
    if ($accelerated && $this->isSuccessful()) {
      $this->headers->set('X-Accel-Redirect', $this->internal_uri);
      $this->maxlen = 0;
    }

    return $this;
  }
}
