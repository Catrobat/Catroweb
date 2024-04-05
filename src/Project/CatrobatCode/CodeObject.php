<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode;

class CodeObject
{
  private ?string $name = null;

  private array $scripts = [];

  private array $codeObjects = [];

  public function getName(): ?string
  {
    return $this->name;
  }

  public function setName(?string $name): void
  {
    $this->name = $name;
  }

  public function addAllScripts(mixed $scriptsToAdd): void
  {
    $this->scripts = $scriptsToAdd;
  }

  public function getCodeObjects(): array
  {
    return $this->codeObjects;
  }

  public function setCodeObjects(mixed $codeObjects): void
  {
    $this->codeObjects = $codeObjects;
  }

  public function getCodeObjectsRecursively(): array
  {
    $objects = [];
    $objects[] = $this;
    foreach ($this->codeObjects as $object) {
      if (null != $object) {
        $objects = $this->addObjectsToArray($objects, $object->getCodeObjectsRecursively());
      }
    }

    return $objects;
  }

  public function addCodeObject(mixed $codeObject): void
  {
    $this->codeObjects[] = $codeObject;
  }

  public function getCode(): string
  {
    $code = '';
    foreach ($this->scripts as $script) {
      $code .= $script->execute();
    }

    return $code;
  }

  public function getScripts(): array
  {
    return $this->scripts;
  }

  private function addObjectsToArray(mixed $objects, mixed $objectsToAdd): array
  {
    foreach ($objectsToAdd as $object) {
      $objects[] = $object;
    }

    return $objects;
  }
}
