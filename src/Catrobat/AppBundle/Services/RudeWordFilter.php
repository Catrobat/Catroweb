<?php

namespace Catrobat\AppBundle\Services;

/**
 * Class RudeWordFilter
 * @package Catrobat\AppBundle\Services
 */
class RudeWordFilter
{
  /**
   * @var \Catrobat\AppBundle\Entity\RudeWordsRepository
   */
  private $repository;

  /**
   * RudeWordFilter constructor.
   *
   * @param \Catrobat\AppBundle\Entity\RudeWordsRepository $repository
   */
  public function __construct(\Catrobat\AppBundle\Entity\RudeWordsRepository $repository)
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
