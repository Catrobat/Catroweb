<?php

declare(strict_types=1);

namespace App\Docs\Search;

use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

/** Tries the docs search from the command line, e.g. against the real embedding model (docs/operations/User-Docs.md). */
#[AsCommand(name: 'catrobat:docs:search', description: 'Search the user docs and show where each result comes from')]
class DocsSearchCommand
{
  public function __construct(private readonly DocsSearch $search)
  {
  }

  public function __invoke(
    OutputInterface $output,
    #[Argument('Words or a question, e.g. "make my cat jump"')] string $query,
    #[Option('Language of the visitor')] string $locale = 'en',
  ): int {
    $result = $this->search->search($query, $locale);
    $output->writeln($result['semantic'] ? 'Hybrid search (keywords and embeddings)' : 'Keyword search only (no embeddings)');
    foreach ($result['groups'] as $type => $hits) {
      foreach ($hits as $hit) {
        $output->writeln(\sprintf('%-8s %-6s %s  (%s)', $type, $hit['locale'], $hit['title'], $hit['path']));
      }
    }

    return Command::SUCCESS;
  }
}
