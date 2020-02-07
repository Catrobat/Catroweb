<?php

namespace App\Catrobat\Requests;


/**
 * Class AddMediaPackageRequest
 * @package App\Catrobat\Requests
 */
class AddMediaPackageRequest
{
  /**
   * @var int
   */
  private $id;
  /**
   * @var string
   */
  private $name_url;
  /**
   * @var string
   */
  private $name;

  /**
   * AddMediaPackageRequest constructor.
   *
   * @param int    $id
   * @param string $name_url
   * @param string $name
   */
  public function __construct($id, $name, $name_url = null)
  {
    $this->name = $name;
    $this->name_url = $name_url;
    $this->id = $id;
  }

  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * @return int
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @return string
   */
  public function getNameUrl()
  {
    return $this->name_url;
  }

}

