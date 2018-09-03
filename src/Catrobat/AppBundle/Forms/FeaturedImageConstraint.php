<?php

namespace Catrobat\AppBundle\Forms;

use Symfony\Component\Validator\Constraint;

class FeaturedImageConstraint extends Constraint
{
  public $required_width = 1024;
  public $required_height = 400;
  public $message = 'The featured image must be of size %width% x %height%';
}
