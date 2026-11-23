<?php

declare(strict_types=1);

namespace App\Docs\Markdown;

use League\CommonMark\Node\Block\AbstractBlock;

/** `::brick{type=SetXBrick}` and its siblings, on a line of their own. */
final class Embed extends AbstractBlock
{
  public function __construct(
    public readonly string $kind,
    public readonly string $value,
  ) {
    parent::__construct();
  }
}
