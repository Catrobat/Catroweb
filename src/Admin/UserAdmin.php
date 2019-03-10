<?php

namespace App\Admin;


use Sonata\Form\Validator\ErrorElement;
use Sonata\UserBundle\Admin\Model\UserAdmin as BaseUserAdmin;


/**
 * Class UserAdmin
 * @package App\Admin
 */
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
   * @param ErrorElement $errorElement
   * @param              $object
   *
   * rewrite validation
   */
  public function validate(ErrorElement $errorElement, $object)
  {
    $errorElement
      ->with('username')
      ->addConstraint(new \Symfony\Component\Validator\Constraints\NotBlank())
      ->addConstraint(new \Symfony\Component\Validator\Constraints\Regex(['pattern' => "/^[\w@_\-\.]+$/"]))
      ->end()
      ->with('email')
      ->addConstraint(new \Symfony\Component\Validator\Constraints\NotBlank())
      ->addConstraint(new \Symfony\Component\Validator\Constraints\Email())
      ->end();
  }

  /**
   * {@inheritdoc}
   */
  public function getRequest()
  {
    if (!$this->request) {
      return $this->request = $this
        ->getConfigurationPool()->getContainer()->get('request_stack')->getCurrentRequest();
    }
    return $this->request;
  }
}