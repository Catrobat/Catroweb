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
  protected $id;

  /**
   * @ORM\Column(type="string")
   */
  protected $word;

  /**
   * @return mixed
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @param mixed $id
   */
  public function setId($id)
  {
    $this->id = $id;
  }

  /**
   * @return mixed
   */
  public function getWord()
  {
    return $this->word;
  }

  /**
   * @param mixed $word
   */
  public function setWord($word)
  {
    $this->word = $word;
  }
}
