<?php

namespace Catrobat\AppBundle\Services;

class RudeWordFilter
{
  private $repository;

  public function __construct(\Catrobat\AppBundle\Entity\RudeWordsRepository $repository)
  {
    $this->repository = $repository;
  }

  public function containsRudeWord($string)
  {
    $string = strtolower($string);
    $rudewords = explode(' ', $string);

    return $this->repository->contains($rudewords);
  }
}
