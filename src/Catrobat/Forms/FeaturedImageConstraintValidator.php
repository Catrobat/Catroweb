<?php

namespace App\Catrobat\Forms;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class FeaturedImageConstraintValidator.
 */
class FeaturedImageConstraintValidator extends ConstraintValidator
{
  /**
   * @param mixed $value
   */
  public function validate($value, Constraint $constraint)
  {
    if (null != $value)
    {
      $imageinfo = getimagesize($value);
      if ($imageinfo[0] != $constraint->required_width || $imageinfo[1] != $constraint->required_height)
      {
        $this->context->buildViolation($constraint->message)
          ->setParameter('%width%', $constraint->required_width)
          ->setParameter('%height%', $constraint->required_height)
          ->addViolation()
        ;
      }
    }
  }
}
