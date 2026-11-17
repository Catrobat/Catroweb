<?php

declare(strict_types=1);

/*
 * Reorders the generated routing.yaml so that a literal path segment always comes before
 * a placeholder at the same position (/projects/search before /projects/{id}).
 * The generator sorts routes by operationId, and Symfony picks the first matching route.
 * Routes are only reordered inside their "# tag" section.
 * Run by code-generation.sh.
 */

$file = __DIR__.'/Server/Resources/config/routing.yaml';
$content = file_get_contents($file);
if (false === $content) {
  throw new RuntimeException('Cannot read '.$file);
}

// "0" per literal segment, "1" per placeholder: /projects/search => "00", /projects/{id} => "01"
$key = static function (string $block): string {
  if (1 !== preg_match('/^ +path: +(\S+)$/m', $block, $m)) {
    throw new RuntimeException("Route without path:\n".$block);
  }

  return implode('', array_map(
    static fn (string $segment): string => str_contains($segment, '{') ? '1' : '0',
    explode('/', trim($m[1], '/')),
  ));
};

$sections = preg_split('/^(?=# \w+$)/m', $content) ?: throw new RuntimeException('Cannot split '.$file);
foreach ($sections as &$section) {
  // "# tag" header line, directly followed by the routes separated by blank lines
  if (1 !== preg_match('/^(# \w+\n)(open_api_server_.*)$/s', $section, $m)) {
    continue;
  }
  $blocks = explode("\n\n", rtrim($m[2], "\n"));
  // Lexicographic, so /projects/user/{id} ("001") sorts before /projects/{id} ("01"). usort is stable.
  usort($blocks, static fn (string $a, string $b): int => strcmp($key($a), $key($b)));
  $section = $m[1].implode("\n\n", $blocks)."\n\n";
}
unset($section);

file_put_contents($file, rtrim(implode('', $sections), "\n")."\n");
