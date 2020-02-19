<?php

namespace App\Catrobat\Services;

/**
 * Class RemixData.
 */
class RemixData
{
  const SCRATCH_DOMAIN = 'scratch.mit.edu';

  /**
   * @var string
   */
  private $remix_url;
  /**
   * @var mixed
   */
  private $remix_url_data;

  /**
   * @param string $remix_url
   */
  public function __construct($remix_url)
  {
    $this->remix_url = $remix_url;
    $this->remix_url_data = parse_url($this->remix_url);
  }

  /**
   * @return string
   */
  public function getUrl()
  {
    return $this->remix_url;
  }

  /**
   * @return string
   */
  public function getProgramId()
  {
    if (!array_key_exists('path', $this->remix_url_data))
    {
      return 0;
    }

    $remix_url_path = $this->remix_url_data['path'];

    $uuid_pattern = '@[a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}@';
    preg_match($uuid_pattern, $remix_url_path, $id_matches);

    if (count($id_matches) > 0)
    {
      return $id_matches[0];
    }

    // legacy id filtering for old projects where ids where numbers
    preg_match('/(\\/[0-9]+(\\/)?)$/', $remix_url_path, $id_matches);
    if (count($id_matches) > 0)
    {
      return str_replace('/', '', $id_matches[0]);
    }

    return '';
  }

  /**
   * @return bool
   */
  public function isScratchProgram()
  {
    if (!array_key_exists('host', $this->remix_url_data))
    {
      return false;
    }

    return false !== strpos($this->remix_url_data['host'], self::SCRATCH_DOMAIN);
  }

  /**
   * @return bool
   */
  public function isAbsoluteUrl()
  {
    return array_key_exists('host', $this->remix_url_data)
      && in_array($this->remix_url_data['scheme'], ['http', 'https'], true);
  }
}
