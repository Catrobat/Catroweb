<?php

namespace App\Entity\Achievements;

use App\Entity\User;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Achievements\UserAchievementRepository")
 * @ORM\Table(
 *     name="user_achievement",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="user_achievement_unique",
 *             columns={"user", "achievement"}
 *         )
 *     }
 * )
 */
class UserAchievement
{
  /**
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected ?int $id = null;

  /**
   * @ORM\ManyToOne(targetEntity="App\Entity\User")
   * @ORM\JoinColumn(name="user", referencedColumnName="id", nullable=false, onDelete="CASCADE")
   */
  protected User $user;

  /**
   * @ORM\ManyToOne(targetEntity="App\Entity\Achievements\Achievement")
   * @ORM\JoinColumn(name="achievement", referencedColumnName="id", nullable=false, onDelete="CASCADE")
   */
  protected Achievement $achievement;

  /**
   * @ORM\Column(name="unlocked_at", type="datetime", nullable=true)
   */
  protected ?DateTime $unlocked_at = null;

  /**
   * @ORM\Column(name="seen_at", type="datetime", nullable=true)
   */
  protected ?DateTime $seen_at = null;

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setId(int $id): UserAchievement
  {
    $this->id = $id;

    return $this;
  }

  public function getUser(): User
  {
    return $this->user;
  }

  public function setUser(User $user): UserAchievement
  {
    $this->user = $user;

    return $this;
  }

  public function getAchievement(): Achievement
  {
    return $this->achievement;
  }

  public function setAchievement(Achievement $achievement): UserAchievement
  {
    $this->achievement = $achievement;

    return $this;
  }

  public function getUnlockedAt(): ?DateTime
  {
    return $this->unlocked_at;
  }

  public function setUnlockedAt(?DateTime $unlocked_at): UserAchievement
  {
    $this->unlocked_at = $unlocked_at;

    return $this;
  }

  public function getSeenAt(): ?DateTime
  {
    return $this->seen_at;
  }

  public function setSeenAt(?DateTime $seen_at): UserAchievement
  {
    $this->seen_at = $seen_at;

    return $this;
  }
}
