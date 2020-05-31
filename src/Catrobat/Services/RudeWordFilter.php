<?php

namespace App\Catrobat\Services;

use App\Repository\RudeWordsRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class RudeWordFilter
{
  private RudeWordsRepository $repository;

  public function __construct(RudeWordsRepository $repository)
  {
    $this->repository = $repository;
  }

  /**
   * @throws NonUniqueResultException
   * @throws NoResultException
   */
  public function containsRudeWord(string $string): bool
  {
    $string = strtolower($string);
    $rude_words = explode(' ', $string);

    return $this->repository->contains($rude_words);
  }
}
