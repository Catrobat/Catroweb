<?php

declare(strict_types=1);

namespace App\Project\Scratch\Converter;

/**
 * Pocket Code loads code.xml with XStream, which allows an object only once: its first occurrence in document
 * order is written in full, every later one is a `reference` to that first occurrence's relative XPath. A
 * reference to something not written yet fails the whole load.
 *
 * The converter writes shared objects (looks, sounds, variables, lists, custom-block signatures) in full
 * wherever they are used, tagged with a `x-ref` key. This pass keeps the first occurrence of each key and turns
 * every later one into a back-reference, so forward references cannot happen.
 *
 * A sprite is written in full only in the object list; a brick naming it ("create clone of") holds an empty
 * element with the sprite's key. When that brick comes first, the sprite's content moves into it, as XStream
 * writes it, and the object list entry becomes the back-reference.
 */
final class XStreamReferences
{
  public const string KEY = 'x-ref';

  /** @var array<string, list<string>> key => absolute path steps of the first occurrence */
  private array $first = [];

  /** @var array<string, \DOMElement> key => first occurrence written with content */
  private array $full = [];

  public static function resolve(\DOMDocument $doc): void
  {
    $root = $doc->documentElement ?? throw new \LogicException('Empty document');
    $self = new self();
    foreach ((new \DOMXPath($doc))->query('//*[@'.self::KEY.'][*]') ?: [] as $full) {
      if ($full instanceof \DOMElement) {
        $self->full[$full->getAttribute(self::KEY)] ??= $full;
      }
    }

    $self->walk($root, [$root->tagName]);
  }

  /**
   * @param list<string> $path steps like "object[3]"; "[1]" is omitted, as XStream does
   */
  private function walk(\DOMElement $element, array $path): void
  {
    $key = $element->getAttribute(self::KEY);
    if ('' !== $key) {
      $element->removeAttribute(self::KEY);
      if (isset($this->first[$key])) {
        $reference = $element->ownerDocument->createElement($element->tagName);
        $reference->setAttribute('reference', self::relative($path, $this->first[$key]));
        $element->parentNode?->replaceChild($reference, $element);

        return;
      }

      $this->first[$key] = $path;
      $full = $this->full[$key] ?? null;
      if (null === $element->firstElementChild && null !== $full) {
        foreach ($full->attributes ?? [] as $attribute) {
          $element->setAttribute($attribute->name, $attribute->value);
        }
        $element->removeAttribute(self::KEY);
        while (null !== $full->firstChild) {
          $element->appendChild($full->firstChild);
        }
      }
    }

    $counts = [];
    foreach (iterator_to_array($element->childNodes) as $child) {
      if (!$child instanceof \DOMElement) {
        continue;
      }

      $counts[$child->tagName] = ($counts[$child->tagName] ?? 0) + 1;
      $step = 1 === $counts[$child->tagName] ? $child->tagName : $child->tagName.'['.$counts[$child->tagName].']';
      $this->walk($child, [...$path, $step]);
    }
  }

  /**
   * @param list<string> $from
   * @param list<string> $to
   */
  private static function relative(array $from, array $to): string
  {
    $common = 0;
    while ($common < \count($from) && $common < \count($to) && $from[$common] === $to[$common]) {
      ++$common;
    }

    $ups = array_fill(0, \count($from) - $common, '..');

    return implode('/', [...$ups, ...\array_slice($to, $common)]);
  }
}
