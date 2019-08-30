<?php

namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 *
 * This entity is designed to assign users to certain or random groups for testing
 * purposes. It has been created in order to not rely on assigning users to groups
 * based on language or location, hence eliminating possible biases. An example use
 * case is assigning users randomly to one of two groups and to present each group
 * another algorithm.
 *
 * Example code with two groups 1 and 2:
 *
 * if ($user != null)
 * {
 *   $user_id = $user->getId();
 *   $em = $this->getDoctrine()->getManager();
 *   $user_test_group = $em->find(UserTestGroup::class, $user_id);
 *   if(!$user_test_group)
 *   {
 *     $user_test_group = new UserTestGroup($user_id, rand(1,2));
 *     $em->persist($user_test_group);
 *     $em->flush();
 *   }
 *
 *   switch ($user_test_group->getGroupId())
 *   {
 *     case 1:
 *       call algorithm 1;
 *       break;
 *     case 2:
 *       call algorithm 2;
 *       break;
 *   }
 * }
 *
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="user_test_group")
 * @UniqueEntity("$user")
 *
 */

class UserTestGroup
{
  /**
   * @ORM\Id
   * @ORM\Column(type="guid", unique=true, nullable=false)
   */
  protected $user_id;

  /**
   * @ORM\Column(type="integer")
   */
  protected $group_number;

  /**
   * @ORM\Column(type="datetime")
   */
  protected $created_at;

  /**
   * @param int $user_id
   * @param int $group_number
   */
  public function __construct($user_id, $group_number)
  {
    if ($user_id !== null)
    {
      $this->setUserId($user_id);
      $this->setGroupNumber($group_number);
    }
  }

  /**
   * @ORM\PrePersist
   */
  public function updateTimestamps()
  {
    if ($this->getCreatedAt() === null)
    {
      $this->setCreatedAt(new \DateTime());
    }
  }

  /**
   * @param int $user_id
   */
  public function setUserId($user_id)
  {
    $this->user_id = $user_id;
  }

  /**
   * @return int
   */
  public function getUserId()
  {
    return $this->user_id;
  }

  /**
   * @param int $group_number
   */
  public function setGroupNumber($group_number)
  {
    $this->group_number = $group_number;
  }

  /**
   * @return int
   */
  public function getGroupNumber()
  {
    return $this->group_number;
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

  /**
   * @return \DateTime
   */
  public function getCreatedAt()
  {
    return $this->created_at;
  }
}
