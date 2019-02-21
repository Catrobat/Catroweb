<?php

namespace Catrobat\AppBundle\Forms;

use Symfony\Component\Validator\Constraint;

/**
 * Class FeaturedImageConstraint
 * @package Catrobat\AppBundle\Forms
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
