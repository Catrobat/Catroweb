<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode;

class SyntaxHighlightingConstants
{
  /**
   * @var string
   */
  final public const VARIABLES = '<span style="color:blueviolet">';
  /**
   * @var string
   */
  final public const VALUE = '<span style="color:limegreen">';
  /**
   * @var string
   */
  final public const OBJECTS = '<span style="color:blue">';
  /**
   * @var string
   */
  final public const FUNCTIONS = '<span style="color:orange">';
  /**
   * @var string
   */
  final public const LOOP = '<span style="color:orangered">';
  /**
   * @var string
   */
  final public const END = '</span>';
}
