<?php

namespace App\Admin\Forms;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class FeaturedImageConstraintValidator extends ConstraintValidator
{
  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint): void
  {
    if (null === $value || !$constraint instanceof FeaturedImageConstraint)
    {
      return;
    }

    /** @var FeaturedImageConstraint $featured_constraint */
    $featured_constraint = $constraint;

    $image_info = getimagesize($value);
    if ($image_info[0] != $featured_constraint->required_width || $image_info[1] != $featured_constraint->required_height)
    {
      $this->context->buildViolation($constraint->message)
        ->setParameter('%width%', (string) $constraint->required_width)
        ->setParameter('%height%', (string) $constraint->required_height)
        ->addViolation()
      ;
    }
  }
}
