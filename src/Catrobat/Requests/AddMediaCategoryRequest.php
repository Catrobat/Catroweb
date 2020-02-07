<?php

namespace App\Catrobat\Requests;


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
   * AddMediaCategoryRequest constructor.
   *
   * @param string $id
   * @param        $name
   * @param null   $priority
   */
  public function __construct($id = '1', $name="", $priority = null)
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

