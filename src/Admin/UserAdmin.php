<?php

namespace App\Admin;

use Sonata\Form\Validator\ErrorElement;
use Sonata\UserBundle\Admin\Model\UserAdmin as BaseUserAdmin;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class UserAdmin extends BaseUserAdmin
{
  /**
   * @return \Symfony\Component\Form\FormBuilder|\Symfony\Component\Form\FormBuilderInterface
   *
   * Override FormBuilder to disable default validation
   */
  public function getFormBuilder()
  {
    $this->formOptions['data_class'] = $this->getClass();

    $options = $this->formOptions;

    $options['validation_groups'] = [];

    $formBuilder = $this->getFormContractor()->getFormBuilder($this->getUniqid(), $options);

    $this->defineFormBuilder($formBuilder);

    return $formBuilder;
  }

  /**
   * rewrite validation.
   *
   * @param mixed $object
   */
  public function validate(ErrorElement $errorElement, $object): void
  {
    $errorElement
      ->with('username')
      ->addConstraint(new NotBlank())
      ->addConstraint(new Regex(['pattern' => '/^[\\w@_\\-\\.]+$/']))
      ->end()
      ->with('email')
      ->addConstraint(new NotBlank())
      ->addConstraint(new Email())
      ->end()
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getRequest()
  {
    if (null === $this->request)
    {
      return $this->request = $this
        ->getConfigurationPool()->getContainer()->get('request_stack')->getCurrentRequest();
    }

    return $this->request;
  }
}
