<?php

namespace App\Catrobat\Requests;

use Symfony\Component\HttpFoundation\File\File;
use App\Entity\MediaPackageCategory;

/**
 * Class AddMediaCategoryRequest
 * @package App\Catrobat\Requests
 */
class AddMediaCategoryRequest
{
  /**
   * @var int
   */
  private $id;
  /**
   * @var int
   */
  private $priority;
  /**
   * @var string
   */
  private $name;

  /**
   * AddMediaLibraryRequest constructor.
   *
   * @param int $id
   * @param int $priority
   * @param string $name
   */
  public function __construct( $id = '1', $name, $priority = null)
  {
    $this->name = $name;
    $this->priority = $priority;
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
   * @return int
   */
  public function getPriority()
  {
    return $this->priority;
  }

}

