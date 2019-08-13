<?php

namespace App\Catrobat\Services;

use App\Repository\RudeWordsRepository;
use Doctrine\ORM\NonUniqueResultException;

/**
 * Class RudeWordFilter
 * @package App\Catrobat\Services
 */
class RudeWordFilter
{
  /**
   * @var RudeWordsRepository
   */
  private $repository;

  /**
   * RudeWordFilter constructor.
   *
   * @param RudeWordsRepository $repository
   */
  public function __construct(RudeWordsRepository $repository)
  {
    $this->repository = $repository;
  }

  /**
   * @param $string
   *
   * @return bool
   * @throws NonUniqueResultException
   */
  public function containsRudeWord($string)
  {
    $string = strtolower($string);
    $rudewords = explode(' ', $string);

    return $this->repository->contains($rudewords);
  }
}
