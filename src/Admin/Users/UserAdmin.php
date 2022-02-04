<?php

namespace App\Admin\Users;

use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\Form\Type\DateTimeRangePickerType;
use Sonata\Form\Validator\ErrorElement;
use Sonata\UserBundle\Admin\Model\UserAdmin as BaseUserAdmin;
use Sonata\UserBundle\Form\Type\SecurityRolesType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserAdmin extends BaseUserAdmin
{
  /**
   * @return FormBuilder|FormBuilderInterface
   *
   * Override FormBuilder to disable default validation
   */
  public function getFormBuilder()
  {
    $this->formOptions['data_class'] = $this->getClass();

    $options = $this->formOptions;

    $options['validation_groups'] = ['Profile'];

    $formBuilder = $this->getFormContractor()->getFormBuilder($this->getUniqid(), $options);

    $this->defineFormBuilder($formBuilder);

    unset($this->listModes['mosaic']);

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
    if (null === $this->request) {
      return $this->request = $this
        ->getConfigurationPool()->getContainer()->get('request_stack')->getCurrentRequest();
    }

    return $this->request;
  }

  /**
   * @param ListMapper $listMapper
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $listMapper): void
  {
    unset($this->listModes['mosaic']);
    $listMapper
      ->addIdentifier('username')
      ->add('email')
      ->add('enabled', null, ['editable' => true])
      ->add('verified', null, ['editable' => true])
      ->add('createdAt')
      ->add('_action', null, [
        'label' => 'Action',
        'actions' => [
          'edit' => [],
          'show' => [],
        ],
      ])
    ;
  }

  protected function configureDatagridFilters(DatagridMapper $filterMapper): void
  {
    $filterMapper
      ->add('username')
      ->add('email')
      ->add('enabled')
      ->add('verified')
      ->add('createdAt', 'doctrine_orm_datetime_range', ['field_type' => DateTimeRangePickerType::class])
      ;
  }

  /**
   * @param FormMapper $formMapper
   *
   * Fields to be shown on create/edit forms
   */
  protected function configureFormFields(FormMapper $formMapper): void
  {
    $formMapper
      ->tab('User')
      ->with('General', ['class' => 'col-md-6'])->end()
      ->end()
      ->tab('Security')
      ->with('Status', ['class' => 'col-md-4'])->end()
      ->with('Roles', ['class' => 'col-md-12'])->end()
      ->end()
    ;

    $formMapper
      ->tab('User')
      ->with('General')
      ->add('username')
      ->add('email')
      ->add('plainPassword', TextType::class, [
        'required' => (!$this->getSubject() || null === $this->getSubject()->getId()),
      ])
      ->end()
      ->end()
      ->tab('Security')
      ->with('Status')
      ->add('enabled', null, ['required' => false])
      ->add('verified', null, ['required' => false, 'label' => 'Verified'])
      ->end()
      ->with('Roles')
      ->add('realRoles', SecurityRolesType::class, [
        'label' => 'form.label_roles',
        'expanded' => true,
        'multiple' => true,
        'required' => false,
      ])
      ->end()
    ;
  }

  protected function configureShowFields(ShowMapper $showMapper): void
  {
    $showMapper
      ->with('General')
      ->add('username')
      ->add('email')
      ->add('createdAt')
      ->end()
      ->with('Security')
      ->add('verified', null, ['label' => 'Verified'])
      ->add('enabled', null, ['label' => 'Enabled'])
      ->end()
    ;
  }
}
