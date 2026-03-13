<?php

namespace OpenAPI\Server\Service;

use Symfony\Component\Validator\Validator\ValidatorInterface as SymfonyValidatorInterface;

class SymfonyValidator implements ValidatorInterface
{
  public function __construct(protected SymfonyValidatorInterface $validator)
  {
  }

  public function validate($value, $constraints = null, $groups = null): \Symfony\Component\Validator\ConstraintViolationListInterface
  {
    return $this->validator->validate($value, $constraints, $groups);
  }
}
