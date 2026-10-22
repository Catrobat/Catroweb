<?php

declare(strict_types=1);

namespace App\Project\Scratch\Converter;

/**
 * A Catrobat formula tree (FormulaElement) and its XStream serialization.
 */
final class Formula
{
  /** Binary operator priorities from Catroid's Operators.java; used to place BRACKET nodes. */
  private const array PRIORITY = [
    'LOGICAL_OR' => 1, 'LOGICAL_AND' => 2,
    'EQUAL' => 3, 'NOT_EQUAL' => 4, 'SMALLER_THAN' => 4, 'SMALLER_OR_EQUAL' => 4, 'GREATER_THAN' => 4, 'GREATER_OR_EQUAL' => 4,
    'PLUS' => 5, 'MINUS' => 5, 'MULT' => 6, 'DIVIDE' => 6, 'POW' => 7,
  ];

  /**
   * @param list<Formula> $extra additionalChildren (third and later function arguments)
   */
  private function __construct(
    public readonly string $type,
    public readonly ?string $value,
    public readonly ?Formula $left = null,
    public readonly ?Formula $right = null,
    public readonly array $extra = [],
  ) {
  }

  /** A literal from Scratch: numbers become NUMBER (negatives as unary MINUS), everything else STRING. */
  public static function literal(mixed $raw): self
  {
    $text = trim((string) $raw);
    if (is_numeric($text) && is_finite((float) $text)) {
      $negative = str_starts_with($text, '-');
      $digits = ltrim($text, '+-');
      if (str_starts_with($digits, '.')) {
        $digits = '0'.$digits;
      }

      $number = new self('NUMBER', $digits);

      return $negative ? self::unary('MINUS', $number) : $number;
    }

    return self::string((string) $raw);
  }

  public static function number(int|float $value): self
  {
    return self::literal((string) $value);
  }

  public static function string(string $value): self
  {
    return new self('STRING', $value);
  }

  public static function op(string $operator, self $left, self $right): self
  {
    return new self('OPERATOR', $operator, $left, $right);
  }

  public static function unary(string $operator, self $operand): self
  {
    return new self('OPERATOR', $operator, null, $operand);
  }

  public static function fn(string $function, ?self $a = null, ?self $b = null, self ...$more): self
  {
    return new self('FUNCTION', $function, $a, $b, array_values($more));
  }

  public static function sensor(string $sensor): self
  {
    return new self('SENSOR', $sensor);
  }

  public static function variable(string $name): self
  {
    return new self('USER_VARIABLE', $name);
  }

  public static function userList(string $name): self
  {
    return new self('USER_LIST', $name);
  }

  public static function collision(string $sprite_name): self
  {
    return new self('COLLISION_FORMULA', $sprite_name);
  }

  public static function input(string $argument_name): self
  {
    return new self('USER_DEFINED_BRICK_INPUT', $argument_name);
  }

  public static function false(): self
  {
    return self::fn('FALSE');
  }

  public function toElement(\DOMDocument $doc, string $name, array $attributes = []): \DOMElement
  {
    $element = $doc->createElement($name);
    foreach ($attributes as $attribute => $value) {
      $element->setAttribute($attribute, $value);
    }

    $extra = $doc->createElement('additionalChildren');
    foreach ($this->extra as $child) {
      $extra->appendChild($child->toElement($doc, 'org.catrobat.catroid.formulaeditor.FormulaElement'));
    }

    $element->appendChild($extra);
    if (null !== $this->left) {
      $element->appendChild($this->bracketed($this->left)->toElement($doc, 'leftChild'));
    }

    if (null !== $this->right) {
      $element->appendChild($this->bracketed($this->right)->toElement($doc, 'rightChild'));
    }

    $element->appendChild($doc->createElement('type', $this->type));
    if (null !== $this->value) {
      $element->appendChild($doc->createElement('value'))->appendChild($doc->createTextNode($this->value));
    }

    return $element;
  }

  /**
   * Pocket Code regenerates infix text from the tree without adding parentheses, so a child operator that binds
   * as loosely as its parent needs an explicit BRACKET, or the first edit in the app changes the meaning.
   */
  private function bracketed(self $child): self
  {
    if ('OPERATOR' !== $this->type || 'OPERATOR' !== $child->type || null === $child->left) {
      return $child;
    }

    $parent_priority = null === $this->left ? 8 : (self::PRIORITY[$this->value] ?? 0); // unary binds tightest
    $child_priority = self::PRIORITY[$child->value] ?? 0;

    return $child_priority <= $parent_priority ? new self('BRACKET', null, null, $child) : $child;
  }
}
