<?php

namespace App\Catrobat\Services;

use App\Repository\RudeWordsRepository;
use Doctrine\ORM\NonUniqueResultException;

/**
 * Class RudeWordFilter.
 */
class RudeWordFilter
{
  /**
   * @var RudeWordsRepository
   */
  private $repository;

  /**
   * RudeWordFilter constructor.
   */
  public function __construct(RudeWordsRepository $repository)
  {
    $this->repository = $repository;
  }

  /**
   * @param $string
   *
   * @throws NonUniqueResultException
   *
   * @return bool
   */
  public function containsRudeWord($string)
  {
    $string = strtolower($string);
    $rudewords = explode(' ', $string);

    return $this->repository->contains($rudewords);
  }
}
