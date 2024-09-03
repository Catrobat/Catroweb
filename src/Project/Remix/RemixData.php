<?php

declare(strict_types=1);

namespace App\Project\Remix;

class RemixData
{
  final public const string SCRATCH_DOMAIN = 'scratch.mit.edu';

  private array $remix_url_data = [];

  public function __construct(private readonly string $remix_url)
  {
    $data = parse_url($this->remix_url);
    if (is_array($data)) {
      $this->remix_url_data = $data;
    }
  }

  public function getUrl(): string
  {
    return $this->remix_url;
  }

  public function getProjectId(): string
  {
    if (!array_key_exists('path', $this->remix_url_data)) {
      return '';
    }

    $remix_url_path = $this->remix_url_data['path'];

    $uuid_pattern = '@[a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}@';
    preg_match($uuid_pattern, (string) $remix_url_path, $id_matches);

    if (count($id_matches) > 0) {
      return $id_matches[0];
    }

    // legacy id filtering for old projects where ids where numbers
    preg_match('#(/\d+(/)?)$#', (string) $remix_url_path, $id_matches);
    if (count($id_matches) > 0) {
      return str_replace('/', '', $id_matches[0]);
    }

    return '';
  }

  public function isScratchProject(): bool
  {
    if (!array_key_exists('host', $this->remix_url_data)) {
      return false;
    }

    return str_contains((string) $this->remix_url_data['host'], self::SCRATCH_DOMAIN);
  }

  public function isAbsoluteUrl(): bool
  {
    return array_key_exists('host', $this->remix_url_data)
      && in_array($this->remix_url_data['scheme'], ['http', 'https'], true);
  }
}
