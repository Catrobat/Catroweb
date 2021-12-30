<?php

namespace App\Api\Services\Base;

use App\Api\Services\ValidationWrapper;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AbstractRequestValidator.
 */
abstract class AbstractRequestValidator implements TranslatorAwareInterface
{
  use TranslatorAwareTrait;

  private ValidatorInterface $validator;
  private ?ValidationWrapper $validationWrapper = null;

  public function __construct(ValidatorInterface $validator, TranslatorInterface $translator)
  {
    $this->validator = $validator;
    $this->initTranslator($translator);
  }

  public function validate(?string $value, ?Email $constraints = null, $groups = null): ConstraintViolationListInterface
  {
    return $this->validator->validate($value, $constraints, $groups);
  }

  public function getValidationWrapper(): ValidationWrapper
  {
    if (is_null($this->validationWrapper)) {
      $this->validationWrapper = new ValidationWrapper();
    }

    return $this->validationWrapper;
  }
}
