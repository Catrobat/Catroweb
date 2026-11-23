<?php

declare(strict_types=1);

namespace App\Docs\Markdown;

use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Parser\Block\AbstractBlockContinueParser;
use League\CommonMark\Parser\Block\BlockContinue;
use League\CommonMark\Parser\Block\BlockContinueParserInterface;
use League\CommonMark\Parser\Block\BlockStart;
use League\CommonMark\Parser\Block\BlockStartParserInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\MarkdownParserStateInterface;

/**
 * Starts an Embed for a line like `::video{youtube=dQw4w9WgXcQ}`. A line with an unknown kind or an invalid value
 * stays a paragraph, so the reader sees the mistake instead of nothing.
 */
final class EmbedParser implements BlockStartParserInterface
{
  /** Kind => [argument, pattern its value must match]. Same kinds and arguments as the docs repo's check. */
  public const array KINDS = [
    'brick' => ['type', '/^\w{1,100}$/'],
    'formula' => ['name', '/^\w{1,100}$/'],
    'project' => ['id', '/^[A-Za-z0-9-]{1,64}$/'],
    'video' => ['youtube', '/^[A-Za-z0-9_-]{11}$/'],
  ];

  #[\Override]
  public function tryStart(Cursor $cursor, MarkdownParserStateInterface $parserState): ?BlockStart
  {
    if ($cursor->isIndented() || 1 !== preg_match('/^::(\w+)\{([^}]*)\}\s*$/', $cursor->getRemainder(), $line)) {
      return BlockStart::none();
    }
    [$argument, $pattern] = self::KINDS[$line[1]] ?? [null, null];
    if (null === $argument || null === $pattern
      || 1 !== preg_match('/(?:^|\s)'.$argument.'=(\S+)/', $line[2], $arg) || 1 !== preg_match($pattern, $arg[1])) {
      return BlockStart::none();
    }
    $cursor->advanceToEnd();

    return BlockStart::of(new class(new Embed($line[1], $arg[1])) extends AbstractBlockContinueParser {
      public function __construct(private readonly Embed $block)
      {
      }

      #[\Override]
      public function getBlock(): AbstractBlock
      {
        return $this->block;
      }

      #[\Override]
      public function tryContinue(Cursor $cursor, BlockContinueParserInterface $activeBlockParser): ?BlockContinue
      {
        return BlockContinue::none();
      }
    })->at($cursor);
  }
}
