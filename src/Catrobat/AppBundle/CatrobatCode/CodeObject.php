<?php

namespace Catrobat\AppBundle\CatrobatCode;


/**
 * Class CodeObject
 * @package Catrobat\AppBundle\CatrobatCode
 */
class CodeObject
{
  /**
   * @var
   */
  private $name;

  /**
   * @var array
   */
  private $scripts;

  /**
   * @var array
   */
  private $codeObjects;

  /**
   * CodeObject constructor.
   */
  public function __construct()
  {
    $this->scripts = [];
    $this->codeObjects = [];
  }

  /**
   * @return mixed
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * @param $name
   */
  public function setName($name)
  {
    $this->name = $name;
  }

  /**
   * @param $scriptsToAdd
   */
  public function addAllScripts($scriptsToAdd)
  {
    foreach ($scriptsToAdd as $script)
    {
      $this->scripts[] = $script;
    }
  }

  /**
   * @return array
   */
  public function getCodeObjects()
  {
    return $this->codeObjects;
  }

  /**
   * @param $codeObjects
   */
  public function setCodeObjects($codeObjects)
  {
    $this->codeObjects = $codeObjects;
  }

  /**
   * @return array
   */
  public function getCodeObjectsRecursively()
  {
    $objects = [];
    $objects[] = $this;
    foreach ($this->codeObjects as $object)
    {
      if ($object != null)
      {
        $objects = $this->addObjectsToArray($objects, $object->getCodeObjectsRecursively());
      }
    }

    return $objects;
  }

  /**
   * @param $objects
   * @param $objectsToAdd
   *
   * @return array
   */
  private function addObjectsToArray($objects, $objectsToAdd)
  {
    foreach ($objectsToAdd as $object)
    {
      $objects[] = $object;
    }

    return $objects;
  }

  /**
   * @param $codeObject
   */
  public function addCodeObject($codeObject)
  {
    $this->codeObjects[] = $codeObject;
  }

  /**
   * @return string
   */
  public function getCode()
  {
    $code = "";
    foreach ($this->scripts as $script)
    {
      $code .= $script->execute();
    }

    return $code;
  }

  /**
   * @return array
   */
  public function getScripts()
  {
    return $this->scripts;
  }
}
