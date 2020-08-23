<?php

namespace App\Entity;

use App\Utils\TimeUtils;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
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
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="user_test_group")
 * @UniqueEntity("$user")
 */
class UserTestGroup
{
  /**
   * @ORM\Id
   * @ORM\Column(type="guid", unique=true, nullable=false)
   */
  protected ?string $user_id = null;

  /**
   * @ORM\Column(type="integer")
   */
  protected ?int $group_number = null;

  /**
   * @ORM\Column(type="datetime")
   */
  protected ?DateTime $created_at = null;

  public function __construct(string $user_id, int $group_number)
  {
    $this->setUserId($user_id);
    $this->setGroupNumber($group_number);
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

  public function setUserId(string $user_id): void
  {
    $this->user_id = $user_id;
  }

  public function getUserId(): ?string
  {
    return $this->user_id;
  }

  public function setGroupNumber(int $group_number): void
  {
    $this->group_number = $group_number;
  }

  public function getGroupNumber(): ?int
  {
    return $this->group_number;
  }

  public function setCreatedAt(DateTime $created_at): UserTestGroup
  {
    $this->created_at = $created_at;

    return $this;
  }

  public function getCreatedAt(): ?DateTime
  {
    return $this->created_at;
  }
}
