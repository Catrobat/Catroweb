<?php

namespace App\Catrobat\Services;

use App\Repository\RudeWordsRepository;
use Doctrine\ORM\NonUniqueResultException;

class RudeWordFilter
{
  private RudeWordsRepository $repository;

  /**
   * RudeWordFilter constructor.
   */
  public function __construct(RudeWordsRepository $repository)
  {
    $this->repository = $repository;
  }

  /**
   * @throws NonUniqueResultException
   */
  public function containsRudeWord(string $string): bool
  {
    $string = strtolower($string);
    $rude_words = explode(' ', $string);

    return $this->repository->contains($rude_words);
  }
}
