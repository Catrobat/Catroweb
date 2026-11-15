<?php

declare(strict_types=1);

namespace App\Moderation;

/** Scans a project's or studio's images for nudity; see ScanContentImagesHandler. */
final readonly class ScanContentImages
{
  public function __construct(public string $content_type, public string $content_id)
  {
  }
}
