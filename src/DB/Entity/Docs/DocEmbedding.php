<?php

declare(strict_types=1);

namespace App\DB\Entity\Docs;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * A docs search vector, keyed by a hash of the model, the prefix and the embedded text (DocsEmbedder::passageKey()),
 * so a sync only embeds new and changed search entries. Entries no longer in the docs are deleted by the sync.
 */
#[ORM\Table(name: 'doc_embedding')]
#[ORM\Entity]
class DocEmbedding
{
  /**
   * @param list<float> $vector
   */
  public function __construct(
    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 32)]
    private string $text_hash,
    #[ORM\Column(type: Types::JSON)]
    private array $vector,
  ) {
  }

  public function getTextHash(): string
  {
    return $this->text_hash;
  }

  /** @return list<float> */
  public function getVector(): array
  {
    return $this->vector;
  }
}
