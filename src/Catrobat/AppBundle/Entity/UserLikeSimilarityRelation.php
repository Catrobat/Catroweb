<?php

namespace Catrobat\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="user_like_similarity_relation")
 * @ORM\Entity(repositoryClass="Catrobat\AppBundle\Entity\UserLikeSimilarityRelationRepository")
 */
class UserLikeSimilarityRelation
{
  /**
   * -----------------------------------------------------------------------------------------------------------------
   * NOTE: this entity uses a Doctrine workaround in order to allow using foreign keys as primary keys
   * @link{http://stackoverflow.com/questions/6383964/primary-key-and-foreign-key-with-doctrine-2-at-the-same-time}
   * -----------------------------------------------------------------------------------------------------------------
   */

  /**
   * @ORM\Id
   * @ORM\Column(type="integer", nullable=false)
   */
  protected $first_user_id;

  /**
   * @ORM\ManyToOne(targetEntity="\Catrobat\AppBundle\Entity\User", inversedBy="relations_of_similar_users_based_on_likes",
   *                                                                fetch="LAZY")
   * @ORM\JoinColumn(name="first_user_id", referencedColumnName="id")
   * @var \Catrobat\AppBundle\Entity\User
   */
  protected $first_user;

  /**
   * @ORM\Id
   * @ORM\Column(type="integer", nullable=false)
   */
  protected $second_user_id;

  /**
   * @ORM\ManyToOne(targetEntity="\Catrobat\AppBundle\Entity\User", inversedBy="reverse_relations_of_similar_users_based_on_likes",
   *                                                                fetch="LAZY")
   * @ORM\JoinColumn(name="second_user_id", referencedColumnName="id")
   * @var \Catrobat\AppBundle\Entity\User
   */
  protected $second_user;

  /**
   * @ORM\Column(type="decimal", precision=4, scale=3, nullable=false, options={"default" = 0.0})
   */
  protected $similarity;

  /**
   * @ORM\Column(type="datetime")
   */
  protected $created_at;

  /**
   * @param User $first_user
   * @param User $second_user
   * @param      $similarity
   */
  public function __construct(User $first_user, User $second_user, $similarity)
  {
    $this->setFirstUser($first_user);
    $this->setSecondUser($second_user);
    $this->similarity = $similarity;
  }

  /**
   * @ORM\PrePersist
   *
   * @throws \Exception
   */
  public function updateTimestamps()
  {
    if ($this->getCreatedAt() == null)
    {
      $this->setCreatedAt(new \DateTime());
    }
  }

  /**
   * @param User $first_user
   *
   * @return $this
   */
  public function setFirstUser(User $first_user)
  {
    $this->first_user = $first_user;
    $this->first_user_id = $first_user->getId();

    return $this;
  }

  /**
   * @param User $second_user
   *
   * @return $this
   */
  public function setSecondUser(User $second_user)
  {
    $this->second_user = $second_user;
    $this->second_user_id = $second_user->getId();

    return $this;
  }

  /**
   * @return User
   */
  public function getFirstUser()
  {
    return $this->first_user;
  }

  /**
   * @return User
   */
  public function getSecondUser()
  {
    return $this->second_user;
  }

  /**
   * @return int
   */
  public function getFirstUserId()
  {
    return $this->first_user_id;
  }

  /**
   * @return int
   */
  public function getSecondUserId()
  {
    return $this->second_user_id;
  }

  /**
   * @param $similarity
   *
   * @return $this
   */
  public function setSimilarity($similarity)
  {
    $this->similarity = (float)$similarity;

    return $this;
  }

  /**
   * @return int
   */
  public function getSimilarity()
  {
    return $this->similarity;
  }

  /**
   * @return \DateTime
   */
  public function getCreatedAt()
  {
    return $this->created_at;
  }

  /**
   * @param \DateTime $created_at
   *
   * @return $this
   */
  public function setCreatedAt(\DateTime $created_at)
  {
    $this->created_at = $created_at;

    return $this;
  }
}
