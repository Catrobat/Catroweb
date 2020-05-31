<?php

namespace App\Catrobat\Services;

class RemixData
{
  /**
   * @var string
   */
  const SCRATCH_DOMAIN = 'scratch.mit.edu';

  private string $remix_url;

  private $remix_url_data;

  public function __construct(string $remix_url)
  {
    $this->remix_url = $remix_url;
    $this->remix_url_data = parse_url($this->remix_url);
  }

  public function getUrl(): string
  {
    return $this->remix_url;
  }

  public function getProgramId(): string
  {
    if (!array_key_exists('path', $this->remix_url_data))
    {
      return '';
    }

    $remix_url_path = $this->remix_url_data['path'];

    $uuid_pattern = '@[a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}@';
    preg_match($uuid_pattern, $remix_url_path, $id_matches);

    if (count($id_matches) > 0)
    {
      return $id_matches[0];
    }

    // legacy id filtering for old projects where ids where numbers
    preg_match('#(\/\d+(\/)?)$#', $remix_url_path, $id_matches);
    if (count($id_matches) > 0)
    {
      return str_replace('/', '', $id_matches[0]);
    }

    return '';
  }

  public function isScratchProgram(): bool
  {
    if (!array_key_exists('host', $this->remix_url_data))
    {
      return false;
    }

    return false !== strpos($this->remix_url_data['host'], self::SCRATCH_DOMAIN);
  }

  public function isAbsoluteUrl(): bool
  {
    return array_key_exists('host', $this->remix_url_data)
      && in_array($this->remix_url_data['scheme'], ['http', 'https'], true);
  }
}
