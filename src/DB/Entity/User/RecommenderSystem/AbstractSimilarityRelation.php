<?php

declare(strict_types=1);

namespace App\DB\Entity\User\RecommenderSystem;

use App\DB\Entity\User\User;
use App\Utils\TimeUtils;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

class AbstractSimilarityRelation
{
  /**
   * -----------------------------------------------------------------------------------------------------------------
   * NOTE: this entity uses a Doctrine workaround in order to allow using foreign keys as primary keys.
   *
   * @see{http://stackoverflow.com/questions/6383964/primary-key-and-foreign-key-with-doctrine-2-at-the-same-time}
   * -----------------------------------------------------------------------------------------------------------------
   */
  #[ORM\Id]
  #[ORM\Column(type: Types::GUID)]
  protected string $first_user_id;

  #[ORM\JoinColumn(name: 'first_user_id', referencedColumnName: 'id')]
  protected User $first_user;

  #[ORM\Id]
  #[ORM\Column(type: Types::GUID)]
  protected string $second_user_id;

  #[ORM\JoinColumn(name: 'second_user_id', referencedColumnName: 'id')]
  protected User $second_user;

  #[ORM\Column(type: Types::DATETIME_MUTABLE)]
  protected ?\DateTime $created_at = null;

  public function __construct(
    User $first_user,
    User $second_user,
    #[ORM\Column(type: Types::DECIMAL, precision: 4, scale: 3, nullable: false, options: ['default' => '0.000'])]
    protected string $similarity,
  ) {
    $this->setFirstUser($first_user);
    $this->setSecondUser($second_user);
  }

  /**
   * @throws \Exception
   */
  #[ORM\PrePersist]
  public function updateTimestamps(): void
  {
    if (!$this->getCreatedAt() instanceof \DateTime) {
      $this->setCreatedAt(TimeUtils::getDateTime());
    }
  }

  public function setFirstUser(User $first_user): self
  {
    $this->first_user = $first_user;
    $this->first_user_id = $first_user->getId();

    return $this;
  }

  public function setSecondUser(User $second_user): self
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

  public function getSimilarity(): float
  {
    return (float) $this->similarity;
  }

  public function getCreatedAt(): ?\DateTime
  {
    return $this->created_at;
  }

  public function setCreatedAt(\DateTime $created_at): self
  {
    $this->created_at = $created_at;

    return $this;
  }
}
