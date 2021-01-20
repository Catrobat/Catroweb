<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity
 * @ORM\Table(name="rudewords")
 * @DoctrineAssert\UniqueEntity(fields="word", message="This word already exists", groups={"rudeword"})
 * @ORM\Entity(repositoryClass="App\Repository\RudeWordsRepository")
 */
class RudeWord
{
  /**
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected ?int $id = null;

  /**
   * @ORM\Column(type="string", unique=true)
   */
  protected string $word = '';

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setId(int $id): void
  {
    $this->id = $id;
  }

  public function getWord(): string
  {
    return $this->word;
  }

  public function setWord(string $word): void
  {
    $this->word = $word;
  }
}
