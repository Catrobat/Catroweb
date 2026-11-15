<?php

declare(strict_types=1);

namespace App\DB\Entity\System;

use App\DB\EntityRepository\System\BackgroundJobRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * A queued job a client waits for (Scratch import, async upload): the API returns its id with 202 and the client
 * polls GET /api/jobs/{id} until it is done or failed. Fire-and-forget jobs (mail, translations) have no row.
 */
#[ORM\Table(name: 'background_job')]
#[ORM\Index(name: 'background_job_type_subject_state_idx', columns: ['type', 'subject', 'state'])]
#[ORM\Index(name: 'background_job_updated_at_idx', columns: ['updated_at'])]
#[ORM\Entity(repositoryClass: BackgroundJobRepository::class)]
class BackgroundJob
{
  final public const string QUEUED = 'queued';
  final public const string RUNNING = 'running';
  final public const string DONE = 'done';
  final public const string FAILED = 'failed';

  #[ORM\Id]
  #[ORM\Column(type: Types::GUID)]
  private string $id;

  #[ORM\Column(type: Types::STRING, length: 20)]
  private string $state = self::QUEUED;

  /** Project id once done. */
  #[ORM\Column(type: Types::STRING, length: 36, nullable: true)]
  private ?string $result = null;

  /** HTTP status the synchronous API would have answered with, and a message that can be shown to the user. */
  #[ORM\Column(type: Types::INTEGER, nullable: true)]
  private ?int $error_status = null;

  #[ORM\Column(type: Types::STRING, length: 500, nullable: true)]
  private ?string $error_message = null;

  #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
  private \DateTimeImmutable $created_at;

  #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
  private \DateTimeImmutable $updated_at;

  public function __construct(
    #[ORM\Column(type: Types::STRING, length: 30)]
    private string $type,
    /** What the job works on, e.g. the Scratch id: a second request for it joins the queued job. */
    #[ORM\Column(type: Types::STRING, length: 64)]
    private string $subject,
    /** Only this user may see the job; null for anonymous jobs (Scratch import). */
    #[ORM\Column(type: Types::GUID, nullable: true)]
    private ?string $user_id = null,
  ) {
    $this->id = Uuid::uuid4()->toString();
    $this->created_at = $this->updated_at = new \DateTimeImmutable();
  }

  public function getId(): string
  {
    return $this->id;
  }

  public function getType(): string
  {
    return $this->type;
  }

  public function getSubject(): string
  {
    return $this->subject;
  }

  public function getUserId(): ?string
  {
    return $this->user_id;
  }

  public function getState(): string
  {
    return $this->state;
  }

  public function getResult(): ?string
  {
    return $this->result;
  }

  public function getErrorStatus(): ?int
  {
    return $this->error_status;
  }

  public function getErrorMessage(): ?string
  {
    return $this->error_message;
  }

  public function getCreatedAt(): \DateTimeImmutable
  {
    return $this->created_at;
  }

  public function getUpdatedAt(): \DateTimeImmutable
  {
    return $this->updated_at;
  }

  public function isFinished(): bool
  {
    return self::DONE === $this->state || self::FAILED === $this->state;
  }

  public function start(): void
  {
    $this->state = self::RUNNING;
    $this->updated_at = new \DateTimeImmutable();
  }

  public function succeed(string $result): void
  {
    $this->state = self::DONE;
    $this->result = $result;
    $this->updated_at = new \DateTimeImmutable();
  }

  public function fail(int $status, string $message): void
  {
    $this->state = self::FAILED;
    $this->error_status = $status;
    $this->error_message = mb_substr($message, 0, 500);
    $this->updated_at = new \DateTimeImmutable();
  }
}
