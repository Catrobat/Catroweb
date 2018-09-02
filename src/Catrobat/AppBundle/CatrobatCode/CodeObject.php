<?php

namespace Catrobat\AppBundle\CatrobatCode;


class CodeObject
{
  private $name;

  private $scripts;

  private $codeObjects;

  public function __construct()
  {
    $this->scripts = [];
    $this->codeObjects = [];
  }

  public function getName()
  {
    return $this->name;
  }

  public function setName($name)
  {
    $this->name = $name;
  }

  public function addAllScripts($scriptsToAdd)
  {
    foreach ($scriptsToAdd as $script)
    {
      $this->scripts[] = $script;
    }
  }

  public function getCodeObjects()
  {
    return $this->codeObjects;
  }

  public function setCodeObjects($codeObjects)
  {
    $this->codeObjects = $codeObjects;
  }

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

  private function addObjectsToArray($objects, $objectsToAdd)
  {
    foreach ($objectsToAdd as $object)
    {
      $objects[] = $object;
    }

    return $objects;
  }

  public function addCodeObject($codeObject)
  {
    $this->codeObjects[] = $codeObject;
  }

  public function getCode()
  {
    $code = "";
    foreach ($this->scripts as $script)
    {
      $code .= $script->execute();
    }

    return $code;
  }

  public function getScripts()
  {
    return $this->scripts;
  }
}
