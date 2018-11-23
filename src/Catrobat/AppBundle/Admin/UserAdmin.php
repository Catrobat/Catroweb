<?php

namespace Catrobat\AppBundle\Admin;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\CoreBundle\Validator\ErrorElement;
use Sonata\UserBundle\Admin\Model\UserAdmin as BaseUserAdmin;


class UserAdmin extends BaseUserAdmin
{

// Override FormBuilder to disable default validation
  public function getFormBuilder()
  {
    $this->formOptions['data_class'] = $this->getClass();

    $options = $this->formOptions;

    $options['validation_groups'] = [];

    $formBuilder = $this->getFormContractor()->getFormBuilder($this->getUniqid(), $options);

    $this->defineFormBuilder($formBuilder);

    return $formBuilder;
  }

  // rewrite validation
  public function validate(ErrorElement $errorElement, $object)
  {
    $errorElement
      ->with('username')
      ->assertNotBlank()
      ->assertRegex(['pattern' => "/^[\w@_\-\.]+$/"])
      ->end()
      ->with('email')
      ->assertNotBlank()
      ->assertEmail()
      ->end();
  }
}