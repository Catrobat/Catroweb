<?php

namespace Catrobat\CoreBundle\Services;


use Catrobat\CoreBundle\Entity\InsultingWords;

class RudeWordFilter {

  private $repository;

    public function __construct(\Catrobat\CoreBundle\Entity\InsultingWordsRepository $repository)
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