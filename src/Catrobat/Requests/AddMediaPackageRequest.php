<?php

namespace App\Catrobat\Requests;

use Symfony\Component\HttpFoundation\File\File;
use App\Entity\MediaPackageCategory;

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
  private $nameurl;
  /**
   * @var string
   */
  private $name;

  /**
   * AddMediaPackageRequest constructor.
   *
   * @param int $id
   * @param string $nameurl
   * @param string $name
   */
  public function __construct( $id, $name, $nameurl = null)
  {
    $this->name = $name;
    $this->nameurl = $nameurl;
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
    return $this->nameurl;
  }

}

