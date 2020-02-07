<?php

namespace App\Catrobat\Requests;

/**
 * Class AddMediaFileRequest
 * @package App\Catrobat\Requests
 */
class AddMediaFileRequest
{
  /**
   * @var string
   */
  private $url;
  /**
   * @var string
   */
  private $author;
  /**
   * @var int
   */
  private $id;
  /**
   * @var string
   */
  private $extension;
  /**
   * @var string
   */
  private $flavor;
  /**
   * @var int
   */
  private $category;
  /**
   * @var string
   */
  private $name;

  /**
   * AddMediaFileRequest constructor.
   *
   * @param $name
   * @param $author
   * @param $id
   * @param $category
   * @param $extension
   * @param $url
   * @param $flavor
   */
  public function __construct($name, $author, $id, $category, $extension, $url, $flavor)
  {
    $this->name = $name;
    $this->author = $author;
    $this->id = $id;
    $this->extension = $extension;
    $this->flavor = $flavor;
    $this->category = $category;
    $this->url = $url;
  }

  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * @return string
   */
  public function getAuthor()
  {
    return $this->author;
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
  public function getExtension()
  {
    return $this->extension;
  }

  /**
   * @return int
   */
  public function getCategory()
  {
    return $this->category;
  }

  /**
   * @return string
   */
  public function getFlavor()
  {
    return $this->flavor;
  }

  /**
   * @return string
   */
  public function getUrl()
  {
    return $this->url;
  }
}

