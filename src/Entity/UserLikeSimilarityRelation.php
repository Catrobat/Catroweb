<?php

namespace App\Entity;

use App\Utils\TimeUtils;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="user_like_similarity_relation")
 * @ORM\Entity(repositoryClass="App\Repository\UserLikeSimilarityRelationRepository")
 */
class UserLikeSimilarityRelation
{
  /**
   * -----------------------------------------------------------------------------------------------------------------
   * NOTE: this entity uses a Doctrine workaround in order to allow using foreign keys as primary keys.
   *
   * @link{http://stackoverflow.com/questions/6383964/primary-key-and-foreign-key-with-doctrine-2-at-the-same-time}
   * -----------------------------------------------------------------------------------------------------------------
   */

  /**
   * @ORM\Id
   * @ORM\Column(type="guid")
   */
  protected string $first_user_id;

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\User", inversedBy="relations_of_similar_users_based_on_likes",
   * fetch="LAZY")
   * @ORM\JoinColumn(name="first_user_id", referencedColumnName="id")
   */
  protected User $first_user;

  /**
   * @ORM\Id
   * @ORM\Column(type="guid")
   */
  protected string $second_user_id;

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\User", inversedBy="reverse_relations_of_similar_users_based_on_likes",
   * fetch="LAZY")
   * @ORM\JoinColumn(name="second_user_id", referencedColumnName="id")
   */
  protected User $second_user;

  /**
   * @ORM\Column(type="decimal", precision=4, scale=3, nullable=false, options={"default": 0.0})
   */
  protected float $similarity;

  /**
   * @ORM\Column(type="datetime")
   */
  protected ?DateTime $created_at = null;

  public function __construct(User $first_user, User $second_user, float $similarity)
  {
    $this->setFirstUser($first_user);
    $this->setSecondUser($second_user);
    $this->similarity = $similarity;
  }

  /**
   * @ORM\PrePersist
   *
   * @throws Exception
   */
  public function updateTimestamps(): void
  {
    if (null === $this->getCreatedAt())
    {
      $this->setCreatedAt(TimeUtils::getDateTime());
    }
  }

  public function setFirstUser(User $first_user): UserLikeSimilarityRelation
  {
    $this->first_user = $first_user;
    $this->first_user_id = $first_user->getId();

    return $this;
  }

  public function setSecondUser(User $second_user): UserLikeSimilarityRelation
  {
    $this->second_user = $second_user;
    $this->second_user_id = $second_user->getId();

    return $this;
  }

  public function getFirstUser(): User
  {
    return $this->first_user;
  }

  public function getSecondUser(): User
  {
    return $this->second_user;
  }

  public function getFirstUserId(): string
  {
    return $this->first_user_id;
  }

  public function getSecondUserId(): string
  {
    return $this->second_user_id;
  }

  public function setSimilarity(float $similarity): UserLikeSimilarityRelation
  {
    $this->similarity = $similarity;

    return $this;
  }

  public function getSimilarity(): float
  {
    return $this->similarity;
  }

  public function getCreatedAt(): ?DateTime
  {
    return $this->created_at;
  }

  public function setCreatedAt(DateTime $created_at): UserLikeSimilarityRelation
  {
    $this->created_at = $created_at;

    return $this;
  }
}
