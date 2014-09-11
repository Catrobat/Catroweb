<?php

namespace Catrobat\CoreBundle\Services;


use Catrobat\CoreBundle\Entity\RudeWords;

class RudeWordFilter {

  private $repository;

    public function __construct(\Catrobat\CoreBundle\Entity\RudeWordsRepository $repository)
    {
      $this->repository = $repository;
    }

  public function containsBadWord($string)
  {
    $string = strtolower($string);
    $badwords = explode(" ", $string);
    return $this->repository->contains($badwords);
  }
} 