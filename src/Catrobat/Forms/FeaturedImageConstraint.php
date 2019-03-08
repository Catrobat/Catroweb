<?php

namespace App\Catrobat\Forms;

use Symfony\Component\Validator\Constraint;

/**
 * Class FeaturedImageConstraint
 * @package App\Catrobat\Forms
 */
class FeaturedImageConstraint extends Constraint
{
  /**
   * @var int
   */
  public $required_width = 1024;
  /**
   * @var int
   */
  public $required_height = 400;
  /**
   * @var string
   */
  public $message = 'The featured image must be of size %width% x %height%';
}
