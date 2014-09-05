<?php

namespace Catrobat\CoreBundle\Services;


use Catrobat\CoreBundle\Entity\InsultingWords;
use Symfony\Component\Security\Core\Util\StringUtils;

class BadWordFilter {

  private $repository;

    public function __construct(\Catrobat\CoreBundle\Entity\InsultingWordsRepository $repository)
    {
      $this->repository = $repository;
    }

  public function containsBadWord($string)
  {
    $badwords = explode(" ", $string);
    return $this->repository->contains($badwords);
  }
} 