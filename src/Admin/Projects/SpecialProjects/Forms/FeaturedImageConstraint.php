<?php

declare(strict_types=1);

namespace App\Admin\Projects\SpecialProjects\Forms;

use Symfony\Component\Validator\Constraint;

class FeaturedImageConstraint extends Constraint
{
  public int $required_width = 1_024;

  public int $required_height = 400;

  public string $message = 'The featured image must be of size %width% x %height%';
}
