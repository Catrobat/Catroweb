<?php

namespace Catrobat\AppBundle\Services;


/**
 * Class RemixData
 * @package Catrobat\AppBundle\Services
 */
class RemixData
{
  /**
   *
   */
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
   * @return int
   */
  public function getProgramId()
  {
    if (!array_key_exists('path', $this->remix_url_data))
    {
      return 0;
    }

    $remix_url_path = $this->remix_url_data['path'];
    preg_match("/(\\/[0-9]+(\\/)?)$/", $remix_url_path, $id_matches);

    return (count($id_matches) > 0) ? intval(str_replace('/', '', $id_matches[0])) : 0;
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

    return (strpos($this->remix_url_data['host'], self::SCRATCH_DOMAIN) !== false);
  }

  /**
   * @return bool
   */
  public function isAbsoluteUrl()
  {
    return array_key_exists('host', $this->remix_url_data)
      && in_array($this->remix_url_data['scheme'], ['http', 'https']);
  }
}
