<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="rudewords")
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
   * @ORM\Column(type="string")
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
