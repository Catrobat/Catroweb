<?php

namespace Catrobat\AppBundle\Forms;

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;

class FeaturedImageConstraintValidator extends ConstraintValidator
{
  public function validate($value, Constraint $constraint)
  {
    if ($value != null)
    {
      $imageinfo = getimagesize($value);
      if ($imageinfo[0] != $constraint->required_width || $imageinfo[1] != $constraint->required_height)
      {
        $this->context->buildViolation($constraint->message)
          ->setParameter('%width%', $constraint->required_width)
          ->setParameter('%height%', $constraint->required_height)
          ->addViolation();
      }
    }
  }
}
