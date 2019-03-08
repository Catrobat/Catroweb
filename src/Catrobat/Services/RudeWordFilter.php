<?php

namespace App\Catrobat\Services;

/**
 * Class RudeWordFilter
 * @package App\Catrobat\Services
 */
class RudeWordFilter
{
  /**
   * @var \App\Repository\RudeWordsRepository
   */
  private $repository;

  /**
   * RudeWordFilter constructor.
   *
   * @param \App\Repository\RudeWordsRepository $repository
   */
  public function __construct(\App\Repository\RudeWordsRepository $repository)
  {
    $this->repository = $repository;
  }

  /**
   * @param $string
   *
   * @return bool
   * @throws \Doctrine\ORM\NonUniqueResultException
   */
  public function containsRudeWord($string)
  {
    $string = strtolower($string);
    $rudewords = explode(' ', $string);

    return $this->repository->contains($rudewords);
  }
}
