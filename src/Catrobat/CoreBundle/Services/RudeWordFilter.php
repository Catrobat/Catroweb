<?php

namespace Catrobat\CoreBundle\Services;


use Catrobat\CoreBundle\Entity\RudeWords;

class RudeWordFilter {

  private $repository;

    public function __construct(\Catrobat\CoreBundle\Entity\RudeWordsRepository $repository)
    {
      $this->repository = $repository;
    }

  public function containsRudeWord($string)
  {
    $string = strtolower($string);
    $rudewords = explode(" ", $string);
    return $this->repository->contains($rudewords);
  }
} 