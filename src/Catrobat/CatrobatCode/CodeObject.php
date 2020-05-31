<?php

namespace App\Catrobat\CatrobatCode;

class CodeObject
{
  private ?string $name = null;

  private array $scripts = [];

  private array $codeObjects = [];

  public function __construct()
  {
    $this->scripts = [];
    $this->codeObjects = [];
  }

  public function getName(): ?string
  {
    return $this->name;
  }

  public function setName(?string $name): void
  {
    $this->name = $name;
  }

  /**
   * @param mixed $scriptsToAdd
   */
  public function addAllScripts($scriptsToAdd): void
  {
    $this->scripts = $scriptsToAdd;
  }

  public function getCodeObjects(): array
  {
    return $this->codeObjects;
  }

  /**
   * @param mixed $codeObjects
   */
  public function setCodeObjects($codeObjects): void
  {
    $this->codeObjects = $codeObjects;
  }

  public function getCodeObjectsRecursively(): array
  {
    $objects = [];
    $objects[] = $this;
    foreach ($this->codeObjects as $object)
    {
      if (null != $object)
      {
        $objects = $this->addObjectsToArray($objects, $object->getCodeObjectsRecursively());
      }
    }

    return $objects;
  }

  /**
   * @param mixed $codeObject
   */
  public function addCodeObject($codeObject): void
  {
    $this->codeObjects[] = $codeObject;
  }

  public function getCode(): string
  {
    $code = '';
    foreach ($this->scripts as $script)
    {
      $code .= $script->execute();
    }

    return $code;
  }

  public function getScripts(): array
  {
    return $this->scripts;
  }

  /**
   * @param mixed $objects
   * @param mixed $objectsToAdd
   */
  private function addObjectsToArray($objects, $objectsToAdd): array
  {
    foreach ($objectsToAdd as $object)
    {
      $objects[] = $object;
    }

    return $objects;
  }
}
